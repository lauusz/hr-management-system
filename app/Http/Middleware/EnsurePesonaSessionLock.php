<?php

namespace App\Http\Middleware;

use ArrayObject;
use Closure;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsurePesonaSessionLock
{
    public function handle(Request $request, Closure $next): Response
    {
        $storeName = config('session.block_store') ?? config('cache.default');
        $driver = config('cache.stores.'.$storeName.'.driver');
        if (! in_array(config('session.driver'), ['file', 'database', 'redis', 'memcached', 'dynamodb'], true)
            || ! in_array($driver, ['database', 'redis'], true)) {
            return $this->unavailable($request);
        }

        try {
            $cache = Cache::store($storeName);
            $store = $cache->getStore();
            $connection = $driver === 'database' ? $store->getConnection() : null;
            $database = $connection?->getConfig('database');
            $memoryDatabase = $connection?->getConfig('driver') === 'sqlite'
                && (! is_string($database) || $database === ':memory:' || str_contains($database, 'mode=memory') || str_contains($database, 'file::memory:'));
            if (! $store instanceof LockProvider || ($connection && ! empty($connection->getConfig('read'))) || $memoryDatabase) {
                abort(503, 'Keranjang dan pengajuan manual sementara tidak tersedia.');
            }
            $scope = hash('sha256', $request->session()->getId().'|'.$request->user()->getAuthIdentifier());
            $key = 'inv_pesona_state_v2:'.$scope;
            $lock = $store->lock('inv_pesona_lock_v2:'.$scope, 300);
            $lock->block(15);
        } catch (\Throwable $exception) {
            Log::warning('State Inventory Pesona tidak tersedia.', ['exception_type' => get_class($exception)]);
            return $this->unavailable($request);
        }

        try {
            try {
                $data = $cache->get($key);
                if ($data === null) {
                    $data = ['cart' => [], 'tokens' => [], 'legacy_notified' => false, 'cart_inflight' => false, 'manual_inflight' => []];
                }
                if (! is_array($data) || ! isset($data['cart'], $data['tokens'], $data['legacy_notified'], $data['cart_inflight'], $data['manual_inflight'])
                    || ! is_array($data['cart']) || ! is_array($data['tokens']) || ! is_bool($data['legacy_notified'])
                    || ! is_bool($data['cart_inflight']) || ! is_array($data['manual_inflight'])) {
                    throw new \RuntimeException('Penyimpanan state Pesona tidak tersedia.');
                }
                $inflightCart = $request->routeIs('inventory.pesona.cart.show') && $request->isMethod('GET') && $data['cart_inflight'];
                if ($request->routeIs('inventory.pesona.cart.*') && $data['cart_inflight'] && ! $inflightCart) {
                    abort(503, 'Pengiriman sebelumnya belum dapat dipastikan. Periksa riwayat pengajuan sebelum mencoba lagi.');
                }
                $cartSubmit = $request->routeIs('inventory.pesona.cart.submit') && $data['cart'] !== [];
                $token = $request->routeIs('inventory.pesona.admin.requests.manual.store') ? $request->input('form_token') : null;
                $manualSubmit = is_string($token) && isset($data['tokens'][$token]);
                if ($manualSubmit && isset($data['manual_inflight'][$token])) {
                    abort(503, 'Pengiriman sebelumnya belum dapat dipastikan. Periksa riwayat pengajuan sebelum mencoba lagi.');
                }
                if ($cartSubmit) {
                    $data['cart_inflight'] = true;
                }
                if ($manualSubmit) {
                    $data['manual_inflight'][$token] = true;
                }
                $data['_touch'] = hrtime(true);
                if (! $inflightCart && ! $cache->put($key, $data, 86400)) {
                    throw new \RuntimeException('Penyimpanan state Pesona tidak tersedia.');
                }
            } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
                throw $exception;
            } catch (\Throwable $exception) {
                Log::warning('State Inventory Pesona tidak tersedia.', ['exception_type' => get_class($exception)]);
                return $this->unavailable($request);
            }

            if ($inflightCart) {
                return response()->view('inventory.pesona.cart-unavailable', ['inflight' => true], 503);
            }

            $state = new ArrayObject($data);
            $request->attributes->set('pesona_state', $state);
            try {
                $response = $next($request);
            } catch (\Throwable $exception) {
                if ($cartSubmit) {
                    $state['cart_inflight'] = false;
                }
                if ($manualSubmit) {
                    $inflight = $state['manual_inflight'];
                    unset($inflight[$token]);
                    $state['manual_inflight'] = $inflight;
                }
                try {
                    $state['_touch'] = hrtime(true);
                    if (! $cache->put($key, $state->getArrayCopy(), 86400)) {
                        throw new \RuntimeException('Penyimpanan state Pesona gagal.');
                    }
                } catch (\Throwable $cacheException) {
                    Log::warning('State Inventory Pesona tidak tersedia.', ['exception_type' => get_class($cacheException)]);
                    report($exception);
                    return $this->unavailable($request, 'Penyimpanan sementara tidak tersedia. Periksa riwayat pengajuan sebelum mencoba lagi.');
                }
                throw $exception;
            }
            if ($cartSubmit) {
                $state['cart_inflight'] = false;
            }
            if ($manualSubmit) {
                $inflight = $state['manual_inflight'];
                unset($inflight[$token]);
                $state['manual_inflight'] = $inflight;
            }
            try {
                $state['_touch'] = hrtime(true);
                if (! $cache->put($key, $state->getArrayCopy(), 86400)) {
                    throw new \RuntimeException('Penyimpanan state Pesona gagal.');
                }
            } catch (\Throwable $exception) {
                Log::warning('State Inventory Pesona tidak tersedia.', ['exception_type' => get_class($exception)]);
                return $this->unavailable($request, 'Penyimpanan sementara tidak tersedia. Periksa riwayat pengajuan sebelum mencoba lagi.');
            }

            return $response;
        } finally {
            try {
                $lock->release();
            } catch (\Throwable $exception) {
                Log::warning('Lock Inventory Pesona tidak dapat dilepas.', ['exception_type' => get_class($exception)]);
            }
        }
    }

    private function unavailable(Request $request, string $message = 'Keranjang dan pengajuan manual sementara tidak tersedia.'): Response
    {
        if ($request->routeIs('inventory.pesona.cart.show') && $request->isMethod('GET')) {
            return response()->view('inventory.pesona.cart-unavailable', ['inflight' => false], 503);
        }

        abort(503, $message);
    }
}
