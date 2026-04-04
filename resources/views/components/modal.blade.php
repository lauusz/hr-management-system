<?php
// Tentukan method form
$confirmMethod = strtoupper($confirmFormMethod ?? 'POST');
$isSpoofMethod = !in_array($confirmMethod, ['GET', 'POST']);

// Default Colors (Primary - Navy)
$btnBg = '#1e4a8d';
$btnHover = '#163a75';
$btnText = '#ffffff';
$iconColor = '#1e4a8d';
$iconBg = '#eff6ff';

// Logic Variant Warna
if (isset($variant)) {
    if ($variant === 'danger') {
        $btnBg = '#dc2626';
        $btnHover = '#b91c1c';
        $iconColor = '#dc2626';
        $iconBg = '#fef2f2';
    } elseif ($variant === 'success') {
        $btnBg = '#059669';
        $btnHover = '#047857';
        $iconColor = '#059669';
        $iconBg = '#ecfdf5';
    } elseif ($variant === 'warning') {
        $btnBg = '#f59e0b'; // Amber lebih soft
        $btnHover = '#d97706';
        $iconColor = '#f59e0b';
        $iconBg = '#fffbeb';
    } elseif ($variant === 'dark') {
        $btnBg = '#1f2937';
        $btnHover = '#111827';
        $iconColor = '#1f2937';
        $iconBg = '#f3f4f6';
    } elseif ($variant === 'info') {
        $btnBg = '#0ea5e9'; // Sky blue
        $btnHover = '#0284c7';
        $iconColor = '#0ea5e9';
        $iconBg = '#f0f9ff';
    }
}
?>

@props([
    'id',
    'title' => 'Konfirmasi',
    'type' => 'confirm',        // Opsi: 'confirm' (default), 'info', 'form'
    'variant' => 'primary',
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
        style="background:#fff; border-radius:16px; box-shadow:0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); width:100%; max-width:480px; max-height:calc(100vh - 32px); display:flex; flex-direction:column; position:relative; overflow:hidden;">

        {{-- HEADER --}}
        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:12px; background:#fff;">
            @if($variant !== 'primary')
            <div style="width:36px; height:36px; border-radius:10px; background:{{ $iconBg }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                @if($variant === 'danger')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                @elseif($variant === 'success')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($variant === 'warning')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                @elseif($variant === 'dark')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($variant === 'info')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @endif
            </div>
            @endif
            <h3 id="{{ $id }}_title" style="margin:0; font-size:1rem; font-weight:700; color:#111827; flex:1;">
                {{ $title }}
            </h3>
            <button
                type="button"
                data-modal-close="true"
                aria-label="Tutup"
                style="border:none; background:transparent; color:#9ca3af; cursor:pointer; padding:6px; border-radius:8px; transition:all 0.2s; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- BODY --}}
        <div style="padding:20px; font-size:0.9rem; color:#4b5563; line-height:1.6; overflow-y:auto;">
            {{ $slot }}
        </div>

        {{-- FOOTER: LOGIKA PENGAMAN (Hanya render jika BUKAN type 'form') --}}
        @if($type !== 'form')
            <div style="padding:16px 20px; background:#f9fafb; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end; align-items:center; gap:10px; flex-wrap:wrap;">
                
                {{-- CASE 1: FORM CONFIRMATION (Standard) --}}
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

                {{-- FALLBACK (Just Close) --}}
                @else
                    <button
                        type="button"
                        data-modal-close="true"
                        style="padding:9px 16px; border-radius:8px; border:1px solid #d1d5db; background:#fff; color:#374151; font-size:0.9rem; font-weight:600; cursor:pointer;">
                        {{ $cancelLabel }}
                    </button>
                @endif
            </div>
        @else
            {{-- Jika TYPE='FORM', kita beri sedikit jarak bawah, tombol dikontrol manual dari slot --}}
            <div style="padding-bottom: 24px;"></div>
        @endif
    </div>
</div>