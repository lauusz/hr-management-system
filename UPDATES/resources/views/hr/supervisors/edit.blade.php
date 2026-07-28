<x-app title="Edit Jabatan Supervisor">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Edit Supervisor & Manager</h1>
                <p class="section-subtitle">Perbarui level akses supervisor atau manager</p>
            </div>
        </div>
    </x-slot>

    <div class="spve-page">
        @if ($errors->any())
            <div class="spve-alert" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <a href="{{ route('hr.supervisors.index') }}" class="back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            Kembali ke Supervisor
        </a>

        <section class="spve-identity">
            <div class="spve-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
            <div class="spve-identity__body">
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->position->name ?? 'Tanpa Jabatan' }} · {{ $user->division->name ?? 'Tanpa Divisi' }}</span>
            </div>
            @if($user->role === \App\Enums\UserRole::MANAGER)
                <span class="spve-badge spve-badge--manager">MANAGER</span>
            @else
                <span class="spve-badge spve-badge--supervisor">SUPERVISOR</span>
            @endif
        </section>

        <section class="spve-card">
            <div class="spve-card__head">
                <div class="spve-card__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.12 2.12 0 013 3L8 18l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                </div>
                <div>
                    <h2>Ubah Level Jabatan</h2>
                    <p>Tentukan level akses baru untuk {{ $user->name }}.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('hr.supervisors.update', $user->id) }}" class="spve-form">
                @csrf
                @method('PUT')

                <div class="spve-field">
                    <label for="role">Level Jabatan <span>*</span></label>
                    <select id="role" name="role" required>
                        <option value="SUPERVISOR" @selected(old('role', $user->role->value) == 'SUPERVISOR')>SUPERVISOR</option>
                        <option value="MANAGER" @selected(old('role', $user->role->value) == 'MANAGER')>MANAGER</option>
                    </select>
                </div>

                <div class="spve-actions">
                    <a href="{{ route('hr.supervisors.index') }}" class="spve-btn-secondary">Batal</a>
                    <button type="submit" class="spve-btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <path d="M17 21v-8H7v8M7 3v5h8"/>
                        </svg>
                        Update Jabatan
                    </button>
                </div>
            </form>
        </section>
    </div>

    <style>
        .section-header-inline{display:flex;align-items:center;gap:10px}
        .section-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .section-title{margin:0;font-size:1rem;font-weight:800;color:var(--text-primary,#111827);letter-spacing:-.01em;line-height:1.25}
        .section-subtitle{margin:0;font-size:.8125rem;color:var(--text-muted,#6B7280);font-weight:500;line-height:1.35}
        .icon-navy{background:rgba(10,61,98,.08);color:var(--primary-dark,#0A3D62)}
        .spve-page{--spve-primary:var(--primary,#145DA0);--spve-primary-dark:var(--primary-dark,#0A3D62);--spve-text:var(--text-primary,#111827);--spve-muted:var(--text-muted,#6B7280);--spve-border:var(--border-light,#E5E7EB);width:100%;box-sizing:border-box;padding:20px 0 48px;color:var(--spve-text)}
        .spve-alert{display:flex;align-items:center;gap:10px;padding:12px 14px;margin-bottom:16px;border:1px solid rgba(220,38,38,.2);border-radius:12px;background:rgba(239,68,68,.08);color:#dc2626;font-size:.82rem;font-weight:600}.spve-alert svg{flex-shrink:0}
        .back-btn{display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 12px 0 10px;margin-bottom:16px;background:var(--white,#fff);border:1px solid var(--border-light,#E5E7EB);border-radius:10px;color:var(--text-muted,#6B7280);text-decoration:none;transition:all .15s ease;box-shadow:0 1px 2px rgba(0,0,0,.04);font-size:.75rem;font-weight:600}.back-btn:hover{border-color:var(--spve-primary);color:var(--spve-primary);background:var(--gray-50,#F5F7FA)}.back-btn svg{transition:transform .2s ease}.back-btn:hover svg{transform:translateX(-2px)}
        .spve-identity{display:flex;align-items:center;gap:14px;padding:16px 18px;margin-bottom:16px;background:#fff;border:1px solid var(--spve-border);border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.04)}.spve-avatar{width:46px;height:46px;display:grid;place-items:center;flex-shrink:0;border-radius:12px;background:rgba(10,61,98,.08);color:var(--spve-primary-dark);font-weight:800}.spve-identity__body{min-width:0;flex:1}.spve-identity__body strong,.spve-identity__body span{display:block}.spve-identity__body strong{font-size:.92rem}.spve-identity__body span{margin-top:3px;color:var(--spve-muted);font-size:.76rem}.spve-badge{display:inline-flex;padding:4px 9px;border-radius:999px;font-size:.66rem;font-weight:800;letter-spacing:.04em}.spve-badge--manager{background:rgba(20,93,160,.09);color:var(--spve-primary)}.spve-badge--supervisor{background:#fff7e8;color:#b4690e}
        .spve-card{overflow:hidden;background:#fff;border:1px solid var(--spve-border);border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .spve-card__head{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid var(--spve-border)}.spve-card__icon{width:36px;height:36px;display:grid;place-items:center;flex-shrink:0;border-radius:10px;background:rgba(10,61,98,.08);color:var(--spve-primary-dark)}.spve-card__head h2{margin:0 0 3px;font-size:.95rem}.spve-card__head p{margin:0;color:var(--spve-muted);font-size:.78rem}
        .spve-form{padding:20px}.spve-field{margin-bottom:18px}.spve-field label{display:block;margin-bottom:6px;color:var(--spve-text);font-size:.8rem;font-weight:700}.spve-field label span{color:#dc2626}.spve-field select{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid var(--spve-border);border-radius:10px;background:#fff;color:var(--spve-text);font:inherit;font-size:.86rem;transition:border-color .15s ease,box-shadow .15s ease}.spve-field select:focus{outline:0;border-color:var(--spve-primary);box-shadow:0 0 0 3px rgba(20,93,160,.1)}
        .spve-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid var(--spve-border)}.spve-btn-primary,.spve-btn-secondary{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 18px;border-radius:10px;font:inherit;font-size:.82rem;font-weight:700;text-decoration:none;cursor:pointer;transition:all .15s ease}.spve-btn-primary{border:1px solid var(--spve-primary);background:var(--spve-primary);color:#fff;box-shadow:0 3px 10px rgba(10,61,98,.18)}.spve-btn-primary:hover{border-color:var(--spve-primary-dark);background:var(--spve-primary-dark)}.spve-btn-secondary{border:1px solid var(--spve-border);background:#fff;color:var(--spve-muted)}.spve-btn-secondary:hover{background:var(--gray-50,#F5F7FA);color:var(--spve-text)}
        @media(max-width:640px){.spve-page{padding:16px 0 88px}.spve-identity{align-items:flex-start;flex-wrap:wrap}.spve-badge{margin-left:60px}.spve-card{border-radius:14px}.spve-card__head,.spve-form{padding:16px}.spve-actions{position:fixed;right:0;bottom:0;left:0;z-index:40;margin:0;padding:12px 16px;background:rgba(255,255,255,.96);border-top:1px solid var(--spve-border);box-shadow:0 -4px 20px rgba(0,0,0,.06);backdrop-filter:blur(8px)}.spve-btn-primary,.spve-btn-secondary{flex:1}}
    </style>
</x-app>
