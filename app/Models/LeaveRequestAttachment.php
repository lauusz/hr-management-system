<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestAttachment extends Model
{
    protected $fillable = [
        'leave_request_id',
        'file_name',
        'original_name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->extension, self::IMAGE_EXTENSIONS, true);
    }
}
