<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'snapshot_name',
        'snapshot_nik',
        'snapshot_position',
        'snapshot_division',
        'snapshot_company',
        'submitted_at',
        'document_path',
        'amount',
        'purpose',
        'repayment_term',
        'disbursement_date',
        'payment_method',
        'status',
        'hrd_id',
        'hrd_decided_at',
        'hrd_note',
    ];

    protected $casts = [
        'submitted_at' => 'date',
        'disbursement_date' => 'date',
        'hrd_decided_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hrd()
    {
        return $this->belongsTo(User::class, 'hrd_id');
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }
}
