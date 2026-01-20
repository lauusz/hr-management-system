<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\LeaveRequest;
use App\Enums\UserRole;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // LOGIKA NOTIFIKASI GLOBAL (View Composer)
        // Logika ini akan berjalan di setiap view untuk menghitung badge notifikasi
        View::composer('*', function ($view) {
            $user = Auth::user();
            $notifCount = 0;

            if ($user) {
                // 1. JIKA USER ADALAH SUPERVISOR ATAU MANAGER
                // Hitung pengajuan bawahan yang statusnya PENDING_SUPERVISOR
                if (in_array($user->role, [UserRole::SUPERVISOR, UserRole::MANAGER])) {
                    $notifCount = LeaveRequest::where('status', LeaveRequest::PENDING_SUPERVISOR)
                        ->whereHas('user', function (Builder $q) use ($user) {
                            $q->where(function ($subQ) use ($user) {
                                // Skenario: Staff -> Supervisor
                                $subQ->where(function ($k) use ($user) {
                                    $k->where('role', UserRole::EMPLOYEE)
                                      ->where('direct_supervisor_id', $user->id);
                                });
                                // Skenario: Supervisor -> Manager
                                $subQ->orWhere(function ($s) use ($user) {
                                    $s->whereIn('role', [UserRole::SUPERVISOR, 'SUPERVISOR'])
                                      ->where('manager_id', $user->id);
                                });
                            });
                        })
                        ->count();
                }
                
                // 2. JIKA USER ADALAH HRD / HR STAFF
                // Hitung yang statusnya PENDING_HR (Verifikasi) ATAU CANCEL_REQ (Request Batal)
                // Pastikan role disesuaikan dengan Enum atau String yang Anda pakai untuk HRD
                elseif (in_array($user->role, [UserRole::HRD, 'HR STAFF'])) {
                    $notifCount = LeaveRequest::whereIn('status', [
                        LeaveRequest::PENDING_HR, 
                        'CANCEL_REQ'
                    ])->count();
                }
            }

            // Kirim variabel $notifCount ke semua view agar bisa dipakai di Sidebar/Navbar
            $view->with('notifCount', $notifCount);
        });
    }
}