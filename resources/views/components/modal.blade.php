<?php
// Tentukan method form
$confirmMethod = strtoupper($confirmFormMethod ?? 'POST');
$isSpoofMethod = !in_array($confirmMethod, ['GET', 'POST']);

// Brand palette — sesuai design system
$btnBg = '#145DA0';
$btnHover = '#0A3D62';
$btnText = '#ffffff';
$iconColor = '#145DA0';
$iconBg = 'rgba(20, 93, 160, 0.08)';

// Logic Variant Warna
if (isset($variant)) {
    if ($variant === 'danger') {
        $btnBg = '#EF4444';
        $btnHover = '#DC2626';
        $iconColor = '#EF4444';
        $iconBg = '#FEF2F2';
    } elseif ($variant === 'success') {
        $btnBg = '#22C55E';
        $btnHover = '#16A34A';
        $iconColor = '#22C55E';
        $iconBg = 'rgba(34, 197, 94, 0.1)';
    } elseif ($variant === 'warning') {
        $btnBg = '#F59E0B';
        $btnHover = '#D97706';
        $iconColor = '#F59E0B';
        $iconBg = 'rgba(245, 158, 11, 0.1)';
    } elseif ($variant === 'dark') {
        $btnBg = '#0A3D62';
        $btnHover = '#082D4A';
        $iconColor = '#0A3D62';
        $iconBg = 'rgba(10, 61, 98, 0.08)';
    } elseif ($variant === 'info') {
        $btnBg = '#3B82F6';
        $btnHover = '#2563EB';
        $iconColor = '#3B82F6';
        $iconBg = 'rgba(59, 130, 246, 0.1)';
    }
}
?>

@props([
    'id',
    'title' => 'Konfirmasi',
    'type' => 'confirm',
    'variant' => 'primary',
    'confirmLabel' => 'Ya',
    'cancelLabel' => 'Batal',
    'confirmFormAction' => null,
    'confirmFormMethod' => 'POST',
    'primaryLinkHref' => null,
    'primaryLinkLabel' => null,
    'hasFile' => false,
])

<div id="{{ $id }}" class="modal-backdrop" style="display:none;">

    <style>
        #{{ $id }}.modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 16px;
            padding-bottom: calc(16px + env(safe-area-inset-bottom));
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            transition: opacity 0.2s ease;
        }

        @keyframes modalPop{{ $id }} {
            0% { opacity: 0; transform: scale(0.96) translateY(8px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        #{{ $id }} .modal-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
            max-height: calc(100vh - 32px);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            animation: modalPop{{ $id }} 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        #{{ $id }} .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #F3F4F6;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #{{ $id }} .modal-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: {{ $iconBg }};
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        #{{ $id }} .modal-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            flex: 1;
            line-height: 1.3;
        }

        #{{ $id }} .modal-close {
            border: none;
            background: transparent;
            color: #9CA3AF;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        #{{ $id }} .modal-close:hover {
            background: #F5F7FA;
            color: #374151;
        }

        #{{ $id }} .modal-body {
            padding: 20px;
            font-size: 0.875rem;
            color: #374151;
            line-height: 1.6;
            overflow-y: auto;
        }

        #{{ $id }} .modal-footer {
            padding: 14px 20px;
            background: #fff;
            border-top: 1px solid #F3F4F6;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        #{{ $id }} .modal-btn {
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        #{{ $id }} .modal-btn-secondary {
            background: #fff;
            border-color: #E5E7EB;
            color: #374151;
        }

        #{{ $id }} .modal-btn-secondary:hover {
            background: #F5F7FA;
            border-color: #D1D5DB;
        }

        #{{ $id }} .modal-btn-primary {
            background: {{ $btnBg }};
            color: {{ $btnText }};
            border-color: transparent;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        #{{ $id }} .modal-btn-primary:hover {
            background: {{ $btnHover }};
        }

        @media (max-width: 480px) {
            #{{ $id }} .modal-footer {
                flex-direction: column-reverse;
                align-items: stretch;
            }
            #{{ $id }} .modal-btn {
                width: 100%;
                padding: 11px 18px;
            }
        }
    </style>

    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}_title">

        {{-- HEADER --}}
        <div class="modal-header">
            @if($variant !== 'primary')
            <div class="modal-icon-wrap">
                @if($variant === 'danger')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                @elseif($variant === 'success')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                @elseif($variant === 'warning')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                @elseif($variant === 'dark')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                @elseif($variant === 'info')
                <svg width="18" height="18" fill="none" stroke="{{ $iconColor }}" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                @endif
            </div>
            @endif
            <h3 id="{{ $id }}_title" class="modal-title">{{ $title }}</h3>
            <button type="button" class="modal-close" data-modal-close="true" aria-label="Tutup">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- BODY --}}
        <div class="modal-body">
            {{ $slot }}
        </div>

        {{-- FOOTER --}}
        @if($type !== 'form')
            <div class="modal-footer">

                {{-- CASE 1: FORM CONFIRMATION --}}
                @if($type === 'confirm' && $confirmFormAction)
                    <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">
                        {{ $cancelLabel }}
                    </button>

                    <form method="{{ in_array($confirmMethod, ['GET','POST']) ? $confirmMethod : 'POST' }}"
                          action="{{ $confirmFormAction }}"
                          @if($hasFile) enctype="multipart/form-data" @endif
                          style="margin:0;">
                        @csrf
                        @if($isSpoofMethod)
                            @method($confirmMethod)
                        @endif
                        <button type="submit" class="modal-btn modal-btn-primary">
                            {{ $confirmLabel }}
                        </button>
                    </form>

                {{-- CASE 2: INFO / LINK --}}
                @elseif($type === 'info')
                    @if($primaryLinkHref && $primaryLinkLabel)
                        <a href="{{ $primaryLinkHref }}" target="_blank" class="modal-btn modal-btn-primary">
                            {{ $primaryLinkLabel }}
                        </a>
                    @endif
                    <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">
                        {{ $cancelLabel }}
                    </button>

                {{-- FALLBACK --}}
                @else
                    <button type="button" class="modal-btn modal-btn-secondary" data-modal-close="true">
                        {{ $cancelLabel }}
                    </button>
                @endif
            </div>
        @else
            <div style="padding-bottom: 24px;"></div>
        @endif
    </div>
</div>
