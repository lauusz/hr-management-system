<x-app title="Tambah Supervisor">
    <x-slot name="header">
        <div class="section-header-inline">
            <div class="section-icon icon-navy">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div>
                <h1 class="section-title">Tambah Supervisor & Manager</h1>
                <p class="section-subtitle">Atur level akses supervisor atau manager baru</p>
            </div>
        </div>
    </x-slot>

    <div class="spvc-page">
        @if ($errors->any())
            <div class="spvc-alert" role="alert">
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

        <section class="spvc-card">
            <div class="spvc-card__head">
                <div class="spvc-card__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </div>
                <div>
                    <h2>Data Supervisor Baru</h2>
                    <p>Pilih karyawan dan tentukan level aksesnya.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('hr.supervisors.store') }}" class="spvc-form">
                @csrf

                <div class="spvc-field">
                    <label for="user_id">Pilih Karyawan <span>*</span></label>
                    <select id="user_id" name="user_id" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($candidates as $c)
                            <option value="{{ $c->id }}" @selected(old('user_id') == $c->id)>
                                {{ $c->name }} - {{ $c->position->name ?? 'Tanpa Jabatan' }} ({{ $c->division->name ?? 'Tanpa Divisi' }})
                            </option>
                        @endforeach
                    </select>
                    <small>Hanya menampilkan karyawan biasa (Employee).</small>
                </div>

                <div class="spvc-field">
                    <label for="role">Level Jabatan Baru <span>*</span></label>
                    <select id="role" name="role" required>
                        <option value="SUPERVISOR" @selected(old('role') == 'SUPERVISOR')>SUPERVISOR</option>
                        <option value="MANAGER" @selected(old('role') == 'MANAGER')>MANAGER</option>
                    </select>
                </div>

                <div class="spvc-actions">
                    <a href="{{ route('hr.supervisors.index') }}" class="spvc-btn-secondary">Batal</a>
                    <button type="submit" class="spvc-btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <path d="M17 21v-8H7v8M7 3v5h8"/>
                        </svg>
                        Simpan
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
        .spvc-page{--spvc-primary:var(--primary,#145DA0);--spvc-primary-dark:var(--primary-dark,#0A3D62);--spvc-text:var(--text-primary,#111827);--spvc-muted:var(--text-muted,#6B7280);--spvc-border:var(--border-light,#E5E7EB);width:100%;box-sizing:border-box;padding:20px 0 48px;color:var(--spvc-text)}
        .spvc-alert{display:flex;align-items:center;gap:10px;padding:12px 14px;margin-bottom:16px;border:1px solid rgba(220,38,38,.2);border-radius:12px;background:rgba(239,68,68,.08);color:#dc2626;font-size:.82rem;font-weight:600}.spvc-alert svg{flex-shrink:0}
        .back-btn{display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 12px 0 10px;margin-bottom:16px;background:var(--white,#fff);border:1px solid var(--border-light,#E5E7EB);border-radius:10px;color:var(--text-muted,#6B7280);text-decoration:none;transition:all .15s ease;box-shadow:0 1px 2px rgba(0,0,0,.04);font-size:.75rem;font-weight:600}.back-btn:hover{border-color:var(--spvc-primary);color:var(--spvc-primary);background:var(--gray-50,#F5F7FA)}.back-btn svg{transition:transform .2s ease}.back-btn:hover svg{transform:translateX(-2px)}
        .spvc-card{overflow:hidden;background:#fff;border:1px solid var(--spvc-border);border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .spvc-card__head{display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid var(--spvc-border)}.spvc-card__icon{width:36px;height:36px;display:grid;place-items:center;flex-shrink:0;border-radius:10px;background:rgba(10,61,98,.08);color:var(--spvc-primary-dark)}.spvc-card__head h2{margin:0 0 3px;font-size:.95rem}.spvc-card__head p{margin:0;color:var(--spvc-muted);font-size:.78rem}
        .spvc-form{padding:20px}.spvc-field{margin-bottom:18px}.spvc-field label{display:block;margin-bottom:6px;color:var(--spvc-text);font-size:.8rem;font-weight:700}.spvc-field label span{color:#dc2626}.spvc-field select{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid var(--spvc-border);border-radius:10px;background:#fff;color:var(--spvc-text);font:inherit;font-size:.86rem;transition:border-color .15s ease,box-shadow .15s ease}.spvc-field select:focus{outline:0;border-color:var(--spvc-primary);box-shadow:0 0 0 3px rgba(20,93,160,.1)}.spvc-field small{display:block;margin-top:6px;color:var(--spvc-muted);font-size:.72rem}
        .spvc-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid var(--spvc-border)}.spvc-btn-primary,.spvc-btn-secondary{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 18px;border-radius:10px;font:inherit;font-size:.82rem;font-weight:700;text-decoration:none;cursor:pointer;transition:all .15s ease}.spvc-btn-primary{border:1px solid var(--spvc-primary);background:var(--spvc-primary);color:#fff;box-shadow:0 3px 10px rgba(10,61,98,.18)}.spvc-btn-primary:hover{border-color:var(--spvc-primary-dark);background:var(--spvc-primary-dark)}.spvc-btn-secondary{border:1px solid var(--spvc-border);background:#fff;color:var(--spvc-muted)}.spvc-btn-secondary:hover{background:var(--gray-50,#F5F7FA);color:var(--spvc-text)}
        @media(max-width:640px){.spvc-page{padding:16px 0 88px}.spvc-card{border-radius:14px}.spvc-card__head,.spvc-form{padding:16px}.spvc-actions{position:fixed;right:0;bottom:0;left:0;z-index:40;margin:0;padding:12px 16px;background:rgba(255,255,255,.96);border-top:1px solid var(--spvc-border);box-shadow:0 -4px 20px rgba(0,0,0,.06);backdrop-filter:blur(8px)}.spvc-btn-primary,.spvc-btn-secondary{flex:1}}
    </style>
</x-app>
