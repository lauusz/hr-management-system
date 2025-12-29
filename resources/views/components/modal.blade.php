<?php

$confirmMethod = strtoupper($confirmFormMethod ?? 'POST');
$isSpoofMethod = !in_array($confirmMethod, ['GET', 'POST']);

?>

@props([
    'id',
    'title' => '',
    'type' => 'confirm',
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
    style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.45);z-index:9999;align-items:center;justify-content:center;padding:12px;padding-bottom:calc(12px + env(safe-area-inset-bottom));backdrop-filter:saturate(120%) blur(3px);-webkit-backdrop-filter:saturate(120%) blur(3px);">
    <div
        class="modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}_title"
        style="background:#fff;border-radius:16px;box-shadow:0 24px 60px rgba(15,23,42,0.4);width:100%;max-width:720px;max-height:calc(100vh - 24px);overflow:hidden;display:flex;flex-direction:column;transform:translateY(0);">
        <div style="padding:12px 14px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:10px;">
            <div id="{{ $id }}_title" style="font-size:0.95rem;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $title }}
            </div>
            <button
                type="button"
                data-modal-close="true"
                aria-label="Tutup"
                style="border:none;background:rgba(17,24,39,0.06);font-size:1.25rem;line-height:1;color:#6b7280;cursor:pointer;padding:10px;border-radius:999px;min-width:40px;min-height:40px;display:flex;align-items:center;justify-content:center;">
                ×
            </button>
        </div>

        @if($type === 'confirm' && $confirmFormAction)
            <form
                method="{{ in_array($confirmMethod, ['GET','POST']) ? $confirmMethod : 'POST' }}"
                action="{{ $confirmFormAction }}"
                @if($hasFile) enctype="multipart/form-data" @endif
                style="margin:0;display:flex;flex-direction:column;flex:1 1 auto;min-height:0;">
                @csrf
                @if($isSpoofMethod)
                    @method($confirmMethod)
                @endif

                <div style="padding:14px 14px 10px 14px;font-size:0.92rem;color:#374151;overflow:auto;flex:1 1 auto;min-height:0;-webkit-overflow-scrolling:touch;">
                    {{ $slot }}
                </div>

                <div style="padding:10px 14px 14px 14px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-shrink:0;flex-wrap:wrap;">
                    <button
                        type="button"
                        data-modal-close="true"
                        style="padding:10px 14px;border-radius:12px;border:1px solid #d1d5db;background:#fff;color:#111827;font-size:0.9rem;font-weight:600;cursor:pointer;min-height:44px;">
                        {{ $cancelLabel }}
                    </button>
                    <button
                        type="submit"
                        style="padding:10px 14px;border-radius:12px;border:1px solid #b91c1c;background:#dc2626;color:#fff;font-size:0.9rem;font-weight:700;cursor:pointer;min-height:44px;">
                        {{ $confirmLabel }}
                    </button>
                </div>
            </form>
        @else
            <div style="padding:14px 14px 10px 14px;font-size:0.92rem;color:#374151;overflow:auto;flex:1 1 auto;min-height:0;-webkit-overflow-scrolling:touch;">
                {{ $slot }}
            </div>

            <div style="padding:10px 14px 14px 14px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;align-items:center;gap:10px;flex-shrink:0;flex-wrap:wrap;">
                @if($type === 'confirm')
                    <button
                        type="button"
                        data-modal-close="true"
                        style="padding:10px 14px;border-radius:12px;border:1px solid #d1d5db;background:#fff;color:#111827;font-size:0.9rem;font-weight:600;cursor:pointer;min-height:44px;">
                        {{ $cancelLabel }}
                    </button>

                    @if($confirmFormAction)
                        <form method="{{ in_array($confirmMethod, ['GET','POST']) ? $confirmMethod : 'POST' }}" action="{{ $confirmFormAction }}" style="margin:0;">
                            @csrf
                            @if($isSpoofMethod)
                                @method($confirmMethod)
                            @endif
                            <button
                                type="submit"
                                style="padding:10px 14px;border-radius:12px;border:1px solid #b91c1c;background:#dc2626;color:#fff;font-size:0.9rem;font-weight:700;cursor:pointer;min-height:44px;">
                                {{ $confirmLabel }}
                            </button>
                        </form>
                    @endif
                @elseif($type === 'info')
                    @if($primaryLinkHref && $primaryLinkLabel)
                        <a
                            href="{{ $primaryLinkHref }}"
                            target="_blank"
                            style="padding:10px 14px;border-radius:12px;border:1px solid #16a34a;background:#22c55e;color:#fff;font-size:0.9rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;min-height:44px;">
                            {{ $primaryLinkLabel }}
                        </a>
                    @endif

                    <button
                        type="button"
                        data-modal-close="true"
                        style="padding:10px 14px;border-radius:12px;border:1px solid #d1d5db;background:#fff;color:#111827;font-size:0.9rem;font-weight:600;cursor:pointer;min-height:44px;">
                        {{ $cancelLabel }}
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
