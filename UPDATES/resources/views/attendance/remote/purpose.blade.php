<x-app title="Keperluan Dinas">
    <x-slot name="header">
        <div class="remote-flow-header">
            <a href="{{ route('remote-attendance.index') }}" class="remote-flow-back" aria-label="Kembali ke Dinas Luar">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1>Keperluan Dinas</h1>
                <p>Langkah 1 dari 2</p>
            </div>
        </div>
    </x-slot>

    <main class="remote-purpose">
        <section class="remote-purpose__form" aria-labelledby="purpose-prompt">
            <p id="purpose-prompt" class="remote-purpose__prompt">Tulis tujuan atau lokasi tugas Anda.</p>

            <label for="remoteNotes">Keterangan</label>
            <textarea id="remoteNotes" maxlength="500" rows="7" placeholder="Contoh: Meeting proyek di PT Nusantara, Surabaya" aria-describedby="notesHelp notesError"></textarea>
            <p id="notesError" class="remote-purpose__error" role="alert" hidden>Keterangan wajib diisi.</p>

            <p id="notesHelp" class="remote-purpose__draft">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 18a4.6 4.6 0 01-.8-9.13A6 6 0 0117.7 7.2 4.5 4.5 0 0118 16H8m4-5v8m0-8l-3 3m3-3l3 3"/>
                </svg>
                Tersimpan sementara <span aria-hidden="true">·</span> Belum dikirim
            </p>
        </section>

        <div class="remote-purpose__dock">
            <button type="button" id="continueButton" class="remote-flow-primary">
                Lanjut ke Foto
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </button>
        </div>
    </main>

    <style>
        .remote-flow-header { display:flex; align-items:center; gap:14px; }
        .remote-flow-back { width:48px; height:48px; display:grid; place-items:center; flex:0 0 auto; border:1px solid var(--border); border-radius:14px; background:var(--white); color:var(--primary-dark); box-shadow:0 4px 12px rgba(15,23,42,.07); }
        .remote-flow-back:focus-visible { outline:3px solid rgba(20,93,160,.25); outline-offset:2px; }
        .remote-flow-header h1 { margin:0; font-size:1.25rem; font-weight:700; line-height:1.2; }
        .remote-flow-header p { margin:3px 0 0; color:var(--text-muted); font-size:.8125rem; }
        .remote-purpose { width:100%; max-width:640px; min-height:620px; display:flex; flex-direction:column; margin:0 auto; border:1px solid var(--border); border-radius:18px; background:var(--white); overflow:hidden; }
        .remote-purpose__form { flex:1; padding:34px 30px; }
        .remote-purpose__prompt { margin:0 0 30px; color:var(--text-secondary); font-size:1rem; line-height:1.55; }
        .remote-purpose label { display:block; margin-bottom:9px; color:var(--text-primary); font-size:.875rem; font-weight:700; }
        .remote-purpose textarea { width:100%; min-height:250px; padding:16px; resize:vertical; border:1px solid #d8dee7; border-radius:14px; color:var(--text-primary); background:var(--white); font:inherit; font-size:.9375rem; line-height:1.55; }
        .remote-purpose textarea:focus { outline:3px solid rgba(20,93,160,.13); border-color:var(--primary); }
        .remote-purpose textarea[aria-invalid="true"] { border-color:var(--error); }
        .remote-purpose__error { margin:7px 0 0; color:#b91c1c; font-size:.75rem; }
        .remote-purpose__draft { display:flex; align-items:center; gap:8px; margin:22px 0 0; color:var(--text-muted); font-size:.75rem; }
        .remote-purpose__dock { padding:20px 24px; border-top:1px solid var(--border-light); background:var(--white); }
        .remote-flow-primary { width:100%; min-height:64px; display:flex; align-items:center; justify-content:center; gap:10px; border:0; border-radius:14px; background:var(--primary-dark); color:var(--white); font:inherit; font-size:1rem; font-weight:700; cursor:pointer; box-shadow:0 6px 16px rgba(10,61,98,.18); }
        .remote-flow-primary:hover { background:#08334f; }
        .remote-flow-primary:focus-visible { outline:3px solid rgba(20,93,160,.28); outline-offset:3px; }
        @media (max-width:767px) {
            .main-content, .content-wrapper { overflow:hidden; }
            .content-wrapper { height:100dvh; display:flex; flex-direction:column; padding:16px 16px max(16px,env(safe-area-inset-bottom)); }
            .topbar { flex:0 0 auto; margin-bottom:16px; }
            .burger { display:none; }
            .remote-purpose { flex:1 1 auto; min-height:0; border:0; border-radius:0; background:transparent; }
            .remote-purpose__form { padding:18px 2px; }
            .remote-purpose__prompt { margin-bottom:28px; }
            .remote-purpose textarea { min-height:0; height:min(42dvh,340px); background:var(--white); }
            .remote-purpose__dock { margin-top:auto; padding:16px 0 0; border-top:1px solid var(--border-light); background:var(--gray-50); }
        }
        @media (max-width:359px), (max-height:700px) and (max-width:767px) {
            .content-wrapper { padding-top:10px; padding-bottom:max(10px,env(safe-area-inset-bottom)); }
            .topbar { margin-bottom:10px; }
            .remote-flow-header { gap:10px; }
            .remote-flow-back { width:44px; height:44px; }
            .remote-flow-header h1 { font-size:1.0625rem; }
            .remote-flow-header p { font-size:.6875rem; }
            .remote-purpose__form { padding-top:12px; }
            .remote-purpose__prompt { margin-bottom:16px; font-size:.875rem; }
            .remote-purpose textarea { height:min(36dvh,220px); padding:12px; }
            .remote-purpose__draft { margin-top:12px; }
            .remote-flow-primary { min-height:56px; }
        }
    </style>

    <script>
        const notesInput = document.getElementById('remoteNotes');
        const notesError = document.getElementById('notesError');
        const draftKey = 'remoteAttendanceNotes';

        notesInput.value = sessionStorage.getItem(draftKey) || '';

        document.getElementById('continueButton').addEventListener('click', () => {
            const notes = notesInput.value.trim();

            if (!notes) {
                notesInput.setAttribute('aria-invalid', 'true');
                notesError.hidden = false;
                notesInput.focus();
                return;
            }

            sessionStorage.setItem(draftKey, notes);
            window.location.href = @json(route('remote-attendance.photo'));
        });

        notesInput.addEventListener('input', () => {
            notesInput.removeAttribute('aria-invalid');
            notesError.hidden = true;
        });
    </script>
</x-app>
