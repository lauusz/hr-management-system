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
])

<div id="{{ $id }}" class="modal-backdrop" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.45);z-index:50;display:none;align-items:center;justify-content:center;padding:16px;">
    <div class="modal-card" style="background:#fff;border-radius:10px;box-shadow:0 20px 40px rgba(15,23,42,0.25);max-width:420px;width:100%;overflow:hidden;">
        <div style="padding:12px 16px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:8px;">
            <div style="font-size:0.95rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $title }}
            </div>
            <button type="button" data-modal-close="true" style="border:none;background:transparent;font-size:1.1rem;line-height:1;color:#6b7280;cursor:pointer;padding:2px 4px;border-radius:999px;">
                ×
            </button>
        </div>

        <div style="padding:16px 16px 8px 16px;font-size:0.9rem;color:#374151;">
            {{ $slot }}
        </div>

        <div style="padding:10px 16px 14px 16px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;align-items:center;gap:10px;">
            @if($type === 'confirm')
                <button type="button" data-modal-close="true" style="padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#111827;font-size:0.85rem;cursor:pointer;">
                    {{ $cancelLabel }}
                </button>

                @if($confirmFormAction)
                    <form method="{{ in_array($confirmMethod, ['GET','POST']) ? $confirmMethod : 'POST' }}" action="{{ $confirmFormAction }}" style="margin:0;">
                        @csrf
                        @if($isSpoofMethod)
                            @method($confirmMethod)
                        @endif
                        <button type="submit" style="padding:6px 12px;border-radius:999px;border:1px solid #b91c1c;background:#dc2626;color:#fff;font-size:0.85rem;cursor:pointer;">
                            {{ $confirmLabel }}
                        </button>
                    </form>
                @endif
            @elseif($type === 'info')
                @if($primaryLinkHref && $primaryLinkLabel)
                    <a href="{{ $primaryLinkHref }}" target="_blank" style="padding:6px 12px;border-radius:999px;border:1px solid #16a34a;background:#22c55e;color:#fff;font-size:0.85rem;text-decoration:none;">
                        {{ $primaryLinkLabel }}
                    </a>
                @endif

                <button type="button" data-modal-close="true" style="padding:6px 10px;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#111827;font-size:0.85rem;cursor:pointer;">
                    {{ $cancelLabel }}
                </button>
            @endif
        </div>
    </div>
</div>
