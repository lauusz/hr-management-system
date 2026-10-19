<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequestAttachment extends Model
{
    protected $fillable = ['file_path', 'original_name', 'mime_type'];

    public function overtimeRequest()
    {
        return $this->belongsTo(OvertimeRequest::class);
    }
}
