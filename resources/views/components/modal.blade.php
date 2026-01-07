<?php
// Tentukan method form
$confirmMethod = strtoupper($confirmFormMethod ?? 'POST');
$isSpoofMethod = !in_array($confirmMethod, ['GET', 'POST']);

// Default Colors (Primary - Navy)
// Ini default jika tidak ada variant yang dipilih
$btnBg = '#1e4a8d'; 
$btnHover = '#163a75';
$btnText = '#ffffff';

// Logic Variant Warna
// Mengubah warna tombol berdasarkan input 'variant'
if (isset($variant)) {
    if ($variant === 'danger') {
        $btnBg = '#dc2626'; // Merah
        $btnHover = '#b91c1c';
    } elseif ($variant === 'success') {
        $btnBg = '#059669'; // Hijau Emerald
        $btnHover = '#047857';
    } elseif ($variant === 'warning') {
        $btnBg = '#d97706'; // Oranye Amber
        $btnHover = '#b45309';
    } elseif ($variant === 'dark') {
        $btnBg = '#1f2937'; // Hitam Abu
        $btnHover = '#111827';
    }
}
?>

@props([
    'id',
    'title' => 'Konfirmasi',
    'type' => 'confirm',        // Opsi: 'confirm' (ada tombol aksi), 'info' (hanya tutup/link)
    'variant' => 'primary',     // Opsi: 'primary', 'danger', 'success', 'warning'
    'confirmLabel' => 'Ya',
    'cancelLabel' => 'Batal',
    'confirmFormAction' => null,
    'confirmFormMethod' => 'POST',
    'primaryLinkHref' => null,
    'primaryLinkLabel' => null,
    'hasFile' => false,
])

<div
    id="{{ $id }}"
    class="modal-backdrop"
    style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px; padding-bottom:calc(16px + env(safe-area-inset-bottom)); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px); transition:opacity 0.2s ease;">
    
    <style>
        @keyframes modalPop {
            0% { opacity: 0; transform: scale(0.95) translateY(8px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-card-anim {
            animation: modalPop 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <div
        class="modal-card modal-card-anim"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}_title"
        style="background:#fff; border-radius:16px; box-shadow:0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); width:100%; max-width:520px; max-height:calc(100vh - 32px); display:flex; flex-direction:column; position:relative; overflow:hidden;">
        
        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; background:#fff;">
            <h3 id="{{ $id }}_title" style="margin:0; font-size:1.05rem; font-weight:700; color:#111827;">
                {{ $title }}
            </h3>
            <button
                type="button"
                data-modal-close="true"
                aria-label="Tutup"
                style="border:none; background:transparent; color:#9ca3af; cursor:pointer; padding:6px; border-radius:8px; transition:all 0.2s; display:flex; align-items:center; justify-content:center;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div style="padding:20px; font-size:0.95rem; color:#4b5563; line-height:1.6; overflow-y:auto;">
            {{ $slot }}
        </div>

        <div style="padding:16px 20px; background:#f9fafb; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end; align-items:center; gap:10px; flex-wrap:wrap;">
            
            {{-- CASE 1: FORM CONFIRMATION --}}
            @if($type === 'confirm' && $confirmFormAction)
                <button
                    type="button"
                    data-modal-close="true"
                    style="padding:9px 16px; border-radius:8px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:600; cursor:pointer; transition:all 0.2s;">
                    {{ $cancelLabel }}
                </button>

                <form
                    method="{{ in_array($confirmMethod, ['GET','POST']) ? $confirmMethod : 'POST' }}"
                    action="{{ $confirmFormAction }}"
                    @if($hasFile) enctype="multipart/form-data" @endif
                    style="margin:0;">
                    @csrf
                    @if($isSpoofMethod)
                        @method($confirmMethod)
                    @endif
                    
                    <button
                        type="submit"
                        onmouseover="this.style.backgroundColor='{{ $btnHover }}'"
                        onmouseout="this.style.backgroundColor='{{ $btnBg }}'"
                        style="padding:9px 20px; border-radius:8px; border:1px solid transparent; background:{{ $btnBg }}; color:{{ $btnText }}; font-size:0.9rem; font-weight:600; cursor:pointer; transition:background-color 0.2s; box-shadow:0 1px 2px 0 rgba(0,0,0,0.05);">
                        {{ $confirmLabel }}
                    </button>
                </form>

            {{-- CASE 2: INFO / LINK --}}
            @elseif($type === 'info')
                
                @if($primaryLinkHref && $primaryLinkLabel)
                    <a href="{{ $primaryLinkHref }}"
                       target="_blank"
                       style="padding:9px 16px; border-radius:8px; background:#1e4a8d; color:#fff; font-size:0.9rem; font-weight:600; text-decoration:none; display:inline-block;">
                       {{ $primaryLinkLabel }}
                    </a>
                @endif

                <button
                    type="button"
                    data-modal-close="true"
                    style="padding:9px 16px; border-radius:8px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:600; cursor:pointer;">
                    {{ $cancelLabel }}
                </button>

            {{-- FALLBACK --}}
            @else
                <button
                    type="button"
                    data-modal-close="true"
                    style="padding:9px 16px; border-radius:8px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:600; cursor:pointer;">
                    {{ $cancelLabel }}
                </button>
            @endif
        </div>
    </div>
</div>