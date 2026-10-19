@if ($overtime->attachments->isNotEmpty())
    <div style="margin-top: 12px; font-size: 0.875rem;">
        <strong>Bukti Pendukung</strong>
        <ul style="margin: 6px 0 0; padding-left: 20px;">
            @foreach ($overtime->attachments as $attachment)
                <li style="overflow-wrap: anywhere; margin-top: 4px;">
                    <a href="{{ route('overtime-requests.attachments.show', [$overtime, $attachment]) }}" target="_blank" rel="noopener noreferrer">{{ $attachment->original_name }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
