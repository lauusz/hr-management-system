<x-app title="Tambah Divisi">

    <div class="div-create-container">

        {{-- Flash / Error Messages --}}
        @if ($errors->any())
        <div class="flash flash-error">
            <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        {{-- Back Link --}}
        <a href="{{ route('hr.organization') }}" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Kembali
        </a>

        {{-- Page Header --}}
        <div class="page-header">
            <div class="page-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <h1 class="page-title">Tambah Divisi Baru</h1>
                <p class="page-subtitle">Buat divisi baru dan tentukan supervisor bila diperlukan.</p>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="form-card">
            <form method="POST" action="{{ route('hr.divisions.store') }}" class="form-layout">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Divisi <span class="req">*</span></label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="form-input"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Finance, Marketing"
                        required>
                </div>

                <div class="form-group">
                    <label for="supervisor">Supervisor</label>
                    <select id="supervisor" name="supervisor_id" class="form-input">
                        <option value="">Tidak ada / Belum ditentukan</option>
                        @foreach ($supervisors as $sup)
                        <option value="{{ $sup->id }}" @selected(old('supervisor_id') == $sup->id)>
                            {{ $sup->name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="form-hint">Opsional. Anda dapat menentukannya nanti.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Divisi
                    </button>
                </div>
            </form>
        </div>

    </div>

    <style>
        /* === BASE VARIABLES === */
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --danger-bg: #fef2f2;
            --danger-text: #b91c1c;
            --danger-border: #fecaca;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* === RESET & BASE === */
        .div-create-container {
            max-width: 560px;
            margin: 0 auto;
            padding: 20px 16px 60px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            color: var(--text-main);
        }

        /* === FLASH MESSAGES === */
        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .flash-error { background: var(--danger-bg); color: var(--danger-text); border: 1px solid var(--danger-border); }
        .flash-icon { width: 18px; height: 18px; flex-shrink: 0; }

        /* === BACK LINK === */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 16px;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }
        .back-link svg { width: 16px; height: 16px; }

        /* === PAGE HEADER === */
        .page-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }
        .page-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: #fff;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .page-icon svg { width: 24px; height: 24px; }
        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .page-subtitle {
            margin: 4px 0 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* === FORM CARD === */
        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        /* === FORM LAYOUT === */
        .form-layout {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .req { color: var(--danger-text); }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            color: var(--text-main);
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-hint {
            display: block;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }
        .btn svg { width: 16px; height: 16px; }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        .form-actions {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
        }

        /* === MOBILE RESPONSIVE === */
        @media (max-width: 640px) {
            .page-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .page-icon {
                margin: 0 auto;
            }
            .form-layout {
                padding: 20px;
            }
            .form-actions {
                flex-direction: column-reverse;
            }
            .btn-primary {
                width: 100%;
            }
        }
    </style>
</x-app>
