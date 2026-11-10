<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaItem;
use App\Models\InvPesonaVehicle;
use App\Models\User;
use App\Services\Inventory\PesonaRequestCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManualRequestController extends Controller
{
    private function eligibleUsers()
    {
        return User::query()->select('users.id', 'users.name', 'users.username', 'users.status')
            ->active()->whereExists(fn ($query) => $query->selectRaw('1')->from('inv_pesona_access_users')->whereColumn('inv_pesona_access_users.user_id', 'users.id'));
    }

    public function create(Request $request): View
    {
        $filters = $request->validate(['user_q' => ['nullable', 'string', 'max:150'], 'item_q' => ['nullable', 'string', 'max:150']]);
        $userSearch = trim($filters['user_q'] ?? '');
        $itemSearch = trim($filters['item_q'] ?? '');
        $users = $this->eligibleUsers()->with('pt')->when($userSearch !== '', fn ($query) => $query->where(fn ($query) => $query->where('users.name', 'like', '%'.$userSearch.'%')->orWhere('users.username', 'like', '%'.$userSearch.'%')))->orderBy('users.name')->orderBy('users.id')->limit(20)->get();
        $items = InvPesonaItem::available()->when($itemSearch !== '', fn ($query) => $query->where('name', 'like', '%'.$itemSearch.'%'))->orderBy('name')->orderBy('id')->limit(20)->get();
        $selectedUserId = old('user_id', $request->query('user_id'));
        $selectedUser = is_scalar($selectedUserId) && filter_var($selectedUserId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false
            ? $this->eligibleUsers()->with('pt')->where('users.id', $selectedUserId)->first() : null;
        $rawRows = old('items', $request->query('items', []));
        $rows = [];
        foreach (is_array($rawRows) ? array_slice($rawRows, 0, 100, true) : [] as $index => $row) {
            $rows[$index] = [
                'item_id' => is_array($row) && isset($row['item_id']) && is_scalar($row['item_id']) ? (string) $row['item_id'] : '',
                'qty' => is_array($row) && isset($row['qty']) && is_scalar($row['qty']) ? (string) $row['qty'] : '',
            ];
        }
        $selectedIds = array_filter(array_column($rows, 'item_id'), fn ($id) => filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false);
        $selectedItems = InvPesonaItem::query()->whereIn('id', $selectedIds)->get()->keyBy('id');
        $tokens = $request->attributes->get('pesona_state')['tokens'];
        $tokens = is_array($tokens) ? array_filter($tokens, fn ($time) => is_int($time) && $time > time() - 7200) : [];
        $state = $request->attributes->get('pesona_state');
        $oldToken = old('form_token');
        if (is_string($oldToken) && isset($tokens[$oldToken]) && ! isset($state['manual_inflight'][$oldToken])) {
            $token = $oldToken;
        } else {
            $tokens = array_slice($tokens, -19, null, true);
            $token = bin2hex(random_bytes(32));
            $tokens[$token] = time();
        }
        $state['manual_inflight'] = array_intersect_key($state['manual_inflight'], $tokens);
        $state['tokens'] = $tokens;

        $vehicles = InvPesonaVehicle::query()->where('is_active', true)->orderBy('nopol')->orderBy('id')->get(['id', 'nopol']);
        return view('inventory.pesona.admin.requests.manual_create', compact('users', 'items', 'selectedUser', 'selectedUserId', 'selectedItems', 'rows', 'token', 'userSearch', 'itemSearch', 'vehicles'));
    }

    public function store(Request $request, PesonaRequestCreator $creator): RedirectResponse
    {
        $data = $request->validate([
            'form_token' => ['required', 'string', 'size:64'],
            'user_id' => ['required', 'integer', 'min:1'],
            'inv_pesona_vehicle_id' => ['required', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['required', 'array'],
            'items.*.item_id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:'.min(PHP_INT_MAX, 4294967295)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], ['items.*.item_id.distinct' => 'Barang yang sama tidak boleh dipilih dua kali.']);
        $tokens = $request->attributes->get('pesona_state')['tokens'];
        if (! is_array($tokens) || ! isset($tokens[$data['form_token']]) || ! is_int($tokens[$data['form_token']]) || $tokens[$data['form_token']] <= time() - 7200) {
            throw ValidationException::withMessages(['form_token' => 'Form sudah digunakan atau kedaluwarsa. Muat ulang halaman.']);
        }
        $user = User::query()->find($data['user_id']);
        if (! $user) {
            throw ValidationException::withMessages(['user_id' => 'Pemohon harus aktif dan memiliki akses Inventory Pesona.']);
        }
        $quantities = [];
        foreach ($data['items'] as $row) {
            $quantities[(int) $row['item_id']] = (int) $row['qty'];
        }
        try {
            $result = $creator->create($user, $quantities, $data['notes'] ?? null, (int) $data['inv_pesona_vehicle_id'], true);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Pengajuan gagal disimpan. Silakan coba lagi.');
        }
        unset($tokens[$data['form_token']]);
        $state = $request->attributes->get('pesona_state');
        $state['tokens'] = $tokens;

        return redirect()->route('inventory.pesona.admin.requests.show', $result['id'])->with('success', 'Pengajuan '.$result['number'].' berhasil dibuat.');
    }
}
