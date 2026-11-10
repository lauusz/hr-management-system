<?php

namespace App\Http\Controllers\Inventory\Pesona\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvPesonaAccessUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $users = User::query()
            ->select('users.id', 'users.name', 'users.username', 'users.status', 'pesona_access.id as pesona_access_id', 'pesona_access.is_admin as pesona_is_admin')
            ->leftJoin('inv_pesona_access_users as pesona_access', 'pesona_access.user_id', '=', 'users.id')
            ->with('pt')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $keyword = '%'.$filters['q'].'%';
                $query->where(function ($query) use ($keyword): void {
                    $query->where('users.name', 'like', $keyword)
                        ->orWhere('users.username', 'like', $keyword)
                        ->orWhereHas('pt', fn ($ptQuery) => $ptQuery->where('name', 'like', $keyword));
                });
            })
            ->when($filters['access'] === 'none', fn ($query) => $query->whereNull('pesona_access.id'))
            ->when($filters['access'] === 'user', fn ($query) => $query->where('pesona_access.is_admin', false))
            ->when($filters['access'] === 'admin', fn ($query) => $query->where('pesona_access.is_admin', true))
            ->orderByDesc('pesona_access.is_admin')
            ->orderByRaw('CASE WHEN pesona_access.id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->paginate(20)->appends($filters);

        $totalUsers = User::query()->count();
        $adminCount = InvPesonaAccessUser::query()->where('is_admin', true)->count();
        $userCount = InvPesonaAccessUser::query()->where('is_admin', false)->count();
        $noneCount = max(0, $totalUsers - $adminCount - $userCount);
        $counts = [
            'all' => $totalUsers,
            'admin' => $adminCount,
            'user' => $userCount,
            'none' => $noneCount,
        ];

        return view('inventory.pesona.admin.access.index', compact('users', 'filters', 'counts'));
    }

    public function grant(Request $request, User $user): RedirectResponse
    {
        $access = DB::transaction(function () use ($request, $user) {
            $current = User::query()->lockForUpdate()->findOrFail($user->id);
            if (! $current->isActive()) {
                return null;
            }

            return InvPesonaAccessUser::firstOrCreate(
                ['user_id' => $current->id],
                ['is_admin' => false, 'created_by' => $request->user()->id],
            );
        });
        if ($access === null) {
            return $this->back($request, 'warning', 'Akses hanya dapat diberikan kepada user aktif.');
        }

        return $this->back($request, $access->wasRecentlyCreated ? 'success' : 'warning',
            $access->wasRecentlyCreated ? 'Akses Pesona berhasil diberikan.' : 'User sudah memiliki akses Pesona.');
    }

    public function grantAdmin(Request $request, User $user): RedirectResponse
    {
        $changed = DB::transaction(function () use ($request, $user) {
            $current = User::query()->lockForUpdate()->findOrFail($user->id);
            if (! $current->isActive()) {
                return null;
            }
            $access = InvPesonaAccessUser::firstOrCreate(
                ['user_id' => $current->id],
                ['is_admin' => true, 'created_by' => $request->user()->id],
            );
            $updated = InvPesonaAccessUser::where('user_id', $current->id)->where('is_admin', false)->update(['is_admin' => true]);

            return $access->wasRecentlyCreated || $updated > 0;
        });
        if ($changed === null) {
            return $this->back($request, 'warning', 'Hak admin hanya dapat diberikan kepada user aktif.');
        }

        return $this->back($request, $changed ? 'success' : 'warning',
            $changed ? 'Hak admin Pesona berhasil diberikan.' : 'User sudah menjadi admin Pesona.');
    }

    public function revokeAdmin(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return $this->back($request, 'warning', 'Hak admin diri sendiri tidak dapat dicabut melalui panel ini.');
        }

        $updated = InvPesonaAccessUser::where('user_id', $user->id)->where('is_admin', true)->update(['is_admin' => false]);

        return $this->back($request, $updated ? 'success' : 'warning',
            $updated ? 'Hak admin dicabut; akses Pesona tetap tersedia.' : 'User tidak memiliki hak admin Pesona.');
    }

    public function revoke(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return $this->back($request, 'warning', 'Akses diri sendiri tidak dapat dicabut melalui panel ini.');
        }

        $deleted = InvPesonaAccessUser::where('user_id', $user->id)->delete();

        return $this->back($request, $deleted ? 'success' : 'warning',
            $deleted ? 'Akses Pesona berhasil dicabut.' : 'User tidak memiliki akses Pesona.');
    }

    private function filters(Request $request): array
    {
        $search = $request->query('q');
        $access = $request->query('access');

        return [
            'q' => is_string($search) ? trim(mb_substr($search, 0, 200)) : '',
            'access' => in_array($access, ['all', 'none', 'user', 'admin'], true) ? $access : 'all',
        ];
    }

    private function back(Request $request, string $type, string $message): RedirectResponse
    {
        return redirect()->route('inventory.pesona.admin.access.index', $this->filters($request))->with($type, $message);
    }
}
