<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AtkRequest extends Model
{
    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_PARTIAL = 'PARTIAL';

    protected $fillable = [
        'request_number',
        'user_id',
        'user_name_snapshot',
        'pt_id',
        'pt_name_snapshot',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pt()
    {
        return $this->belongsTo(Pt::class);
    }

    public function items()
    {
        return $this->hasMany(AtkRequestItem::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public static function createPending(User $user, Collection $rows, ?string $notes = null): self
    {
        $pt = $user->pt;

        return DB::transaction(function () use ($user, $pt, $rows, $notes): self {
            $atkRequest = null;
            $prefix = 'ATK-'.now()->format('Ym').'-';

            for ($attempt = 0; $attempt < 5; $attempt++) {
                $maxSequence = self::query()
                    ->where('request_number', 'like', $prefix.'%')
                    ->pluck('request_number')
                    ->map(function (string $number): int {
                        $suffix = substr(strrchr($number, '-'), 1);

                        return ctype_digit($suffix) ? (int) $suffix : 0;
                    })
                    ->max() ?? 0;

                try {
                    $atkRequest = self::create([
                        'request_number' => $prefix.str_pad((string) ($maxSequence + 1), 4, '0', STR_PAD_LEFT),
                        'user_id' => $user->id,
                        'user_name_snapshot' => $user->name,
                        'pt_id' => $pt?->id,
                        'pt_name_snapshot' => $pt?->name,
                        'status' => self::STATUS_PENDING,
                        'notes' => $notes,
                    ]);
                    break;
                } catch (QueryException $exception) {
                    if (($exception->errorInfo[1] ?? null) !== 1062) {
                        throw $exception;
                    }
                }
            }

            if ($atkRequest === null) {
                throw new \RuntimeException('Gagal membuat nomor pengajuan setelah beberapa percobaan. Coba lagi.');
            }

            foreach ($rows as $row) {
                $item = $row['item'];
                $atkRequest->items()->create([
                    'atk_item_id' => $item->id,
                    'qty' => $row['qty'],
                    'item_name_snapshot' => $item->name,
                    'unit_name_snapshot' => $item->unit_name,
                    'unit_size_snapshot' => $item->unit_size,
                    'content_unit_name_snapshot' => $item->content_unit_name,
                ]);
            }

            return $atkRequest;
        });
    }

    /**
     * Rangkum status request dari status item-itemnya.
     * Dipanggil saat finalisasi review admin.
     *
     * - semua APPROVED  -> APPROVED
     * - semua REJECTED  -> REJECTED
     * - campuran         -> PARTIAL
     * - masih ada PENDING -> PENDING (tidak boleh difinalisasi)
     */
    public function refreshStatusFromItems(): string
    {
        $statuses = $this->items->pluck('status')->unique();

        if ($statuses->contains(AtkRequestItem::STATUS_PENDING)) {
            return self::STATUS_PENDING;
        }

        $allApproved = $statuses->count() === 1 && $statuses->first() === AtkRequestItem::STATUS_APPROVED;
        $allRejected = $statuses->count() === 1 && $statuses->first() === AtkRequestItem::STATUS_REJECTED;

        if ($allApproved) {
            return self::STATUS_APPROVED;
        }

        if ($allRejected) {
            return self::STATUS_REJECTED;
        }

        return self::STATUS_PARTIAL;
    }
}
