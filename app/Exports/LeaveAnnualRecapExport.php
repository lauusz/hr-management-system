<?php

namespace App\Exports;

use App\Enums\LeaveType;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveRequest;
use App\Models\OfficeHoliday;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class LeaveAnnualRecapExport extends DefaultValueBinder implements FromArray, WithCustomValueBinder, WithEvents, WithTitle
{
    /** @var array<string, string> */
    protected array $comments = [];

    /** @var array<string, float> */
    protected array $dateValues = [];

    /**
     * @var array<int, array{
     *     title_row: int,
     *     header_start_row: int,
     *     data_start_row: int,
     *     data_end_row: int
     * }>
     */
    protected array $groupLayouts = [];

    protected int $slotCount = 12;

    public function __construct(
        protected int $year,
        protected LeaveBalanceService $leaveBalanceService,
    ) {}

    public function array(): array
    {
        $this->comments = [];
        $this->dateValues = [];
        $this->groupLayouts = [];

        $users = User::query()
            ->active()
            ->with('profile.pt')
            ->orderBy('name')
            ->get();

        $leaves = $this->eligibleLeaves();
        $holidays = $this->holidaysFor($leaves);
        $entriesByUser = $this->entriesByUser($leaves, $holidays);

        $this->slotCount = max(
            12,
            (int) $entriesByUser->map(fn (Collection $entries): int => $entries->count())->max()
        );

        $rows = [];

        foreach ($this->userGroups($users) as $groupIndex => $group) {
            if ($groupIndex > 0) {
                $rows[] = array_fill(0, $this->slotCount + 1, null);
            }

            $titleRow = count($rows) + 1;
            $rows[] = array_merge([$group['name']], array_fill(0, $this->slotCount, null));

            $headerStartRow = count($rows) + 1;
            $rows[] = array_merge(['Nama', $this->year], array_fill(0, $this->slotCount - 1, null));
            $rows[] = array_merge([null], range(1, $this->slotCount));
            $dataStartRow = count($rows) + 1;

            foreach ($group['users'] as $user) {
                /** @var Collection<int, array{date: Carbon, comment: string}> $entries */
                $entries = $entriesByUser->get($user->id, collect());
                $rowNumber = count($rows) + 1;
                $row = array_merge([$user->name], array_fill(0, $this->slotCount, null));

                foreach ($entries->values() as $index => $entry) {
                    $columnNumber = $index + 2;
                    $excelDate = ExcelDate::dateTimeToExcel($entry['date']);
                    $row[$index + 1] = $excelDate;
                    $coordinate = Coordinate::stringFromColumnIndex($columnNumber).$rowNumber;
                    $this->comments[$coordinate] = $entry['comment'];
                    $this->dateValues[$coordinate] = $excelDate;
                }

                $rows[] = $row;
            }

            $this->groupLayouts[] = [
                'title_row' => $titleRow,
                'header_start_row' => $headerStartRow,
                'data_start_row' => $dataStartRow,
                'data_end_row' => count($rows),
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return (string) $this->year;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex($this->slotCount + 1);

                if ($this->groupLayouts !== []) {
                    $sheet->freezePane('B4');
                }
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getColumnDimension('A')->setWidth(32);

                for ($column = 2; $column <= $this->slotCount + 1; $column++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(14);
                }

                foreach ($this->groupLayouts as $layout) {
                    $titleRow = $layout['title_row'];
                    $headerStartRow = $layout['header_start_row'];
                    $headerEndRow = $headerStartRow + 1;
                    $dataStartRow = $layout['data_start_row'];
                    $dataEndRow = $layout['data_end_row'];

                    $sheet->mergeCells("A{$titleRow}:{$lastColumn}{$titleRow}");
                    $sheet->mergeCells("A{$headerStartRow}:A{$headerEndRow}");
                    $sheet->mergeCells("B{$headerStartRow}:{$lastColumn}{$headerStartRow}");
                    $sheet->getRowDimension($titleRow)->setRowHeight(24);
                    $sheet->getRowDimension($headerStartRow)->setRowHeight(26);
                    $sheet->getRowDimension($headerEndRow)->setRowHeight(22);

                    $sheet->getStyle("A{$titleRow}:{$lastColumn}{$titleRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => 'FFFFFFFF'],
                            'size' => 12,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF1F4E78'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $sheet->getStyle("A{$headerStartRow}:{$lastColumn}{$headerEndRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => 'FFFFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF4472C4'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFD9E2F3'],
                            ],
                        ],
                    ]);

                    if ($dataEndRow >= $dataStartRow) {
                        $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$dataEndRow}")->applyFromArray([
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['argb' => 'FFD9E2F3'],
                                ],
                            ],
                        ]);
                        $sheet->getStyle("B{$dataStartRow}:{$lastColumn}{$dataEndRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("B{$dataStartRow}:{$lastColumn}{$dataEndRow}")
                            ->getNumberFormat()
                            ->setFormatCode('dd/mm/yy');
                    }
                }

                foreach ($this->dateValues as $coordinate => $value) {
                    $sheet->getCell($coordinate)->setValueExplicit($value, DataType::TYPE_NUMERIC);
                }

                foreach ($this->comments as $coordinate => $text) {
                    $comment = $sheet->getComment($coordinate);
                    $comment->setAuthor('HRD System');
                    $comment->setWidth('320pt');
                    $comment->setHeight('120pt');
                    $comment->getText()->createText($text);
                }
            },
        ];
    }

    /**
     * @return Collection<int, array{name: string, without_pt: bool, users: Collection<int, User>}>
     */
    protected function userGroups(Collection $users): Collection
    {
        return $users
            ->groupBy(fn (User $user): string => $user->profile?->pt?->id
                ? 'pt:'.$user->profile->pt->id
                : 'without-pt')
            ->map(function (Collection $groupUsers): array {
                $pt = $groupUsers->first()?->profile?->pt;

                return [
                    'name' => $pt?->name ?? 'Tanpa PT',
                    'without_pt' => $pt === null,
                    'users' => $groupUsers->values(),
                ];
            })
            ->sort(function (array $left, array $right): int {
                if ($left['without_pt'] !== $right['without_pt']) {
                    return $left['without_pt'] ? 1 : -1;
                }

                return strnatcasecmp($left['name'], $right['name']);
            })
            ->values();
    }

    /** @return Collection<int, LeaveRequest> */
    protected function eligibleLeaves(): Collection
    {
        $yearStart = Carbon::create($this->year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($this->year, 12, 31)->endOfDay();

        return LeaveRequest::withoutGlobalScopes()
            ->with(['user', 'leaveBalanceTransactions'])
            ->whereHas('user', fn ($users) => $users->active())
            ->where('start_date', '<=', $yearEnd->toDateString())
            ->where(function ($period) use ($yearStart): void {
                $period->where('end_date', '>=', $yearStart->toDateString())
                    ->orWhere(function ($withoutEndDate) use ($yearStart): void {
                        $withoutEndDate->whereNull('end_date')
                            ->where('start_date', '>=', $yearStart->toDateString());
                    });
            })
            ->where(function ($query): void {
                $query->where(function ($pending): void {
                    $pending->where('status', LeaveRequest::PENDING_HR)
                        ->where('type', LeaveType::CUTI->value);
                })->orWhere(function ($approved): void {
                    $approved->where('status', LeaveRequest::STATUS_APPROVED)
                        ->whereHas('leaveBalanceTransactions', function ($transactions): void {
                            $transactions->whereIn('transaction_type', [
                                LeaveBalanceTransaction::DEDUCT,
                                LeaveBalanceTransaction::ADJUSTMENT,
                                LeaveBalanceTransaction::REFUND,
                            ]);
                        });
                });
            })
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<string, OfficeHoliday> */
    protected function holidaysFor(Collection $leaves): Collection
    {
        if ($leaves->isEmpty()) {
            return collect();
        }

        $start = $leaves->min(fn (LeaveRequest $leave): string => $leave->start_date->toDateString());
        $end = $leaves->max(fn (LeaveRequest $leave): string => ($leave->end_date ?? $leave->start_date)->toDateString());

        return OfficeHoliday::query()
            ->where('is_active', true)
            ->whereDate('holiday_date', '>=', $start)
            ->whereDate('holiday_date', '<=', $end)
            ->get()
            ->keyBy(fn (OfficeHoliday $holiday): string => $holiday->holiday_date->toDateString());
    }

    /**
     * @param  Collection<string, OfficeHoliday>  $holidays
     * @return Collection<int, Collection<int, array{date: Carbon, comment: string}>>
     */
    protected function entriesByUser(Collection $leaves, Collection $holidays): Collection
    {
        $entriesByUser = collect();

        foreach ($leaves as $leave) {
            $effectiveDates = $this->effectiveDates($leave, $holidays);
            $amount = $leave->status === LeaveRequest::PENDING_HR
                ? (float) $effectiveDates->sum('weight')
                : $this->netDeduction($leave);

            if ($amount <= 0) {
                continue;
            }

            $remaining = $amount;
            $includedDates = collect();

            foreach ($effectiveDates as $effectiveDate) {
                if ($remaining <= 0) {
                    break;
                }

                $date = $effectiveDate['date'];
                $allocatedAmount = min($remaining, $effectiveDate['weight']);
                $remaining = round($remaining - $allocatedAmount, 2);

                if ($date->year !== $this->year) {
                    continue;
                }

                $includedDates->push([
                    'date' => $date,
                    'amount' => $allocatedAmount,
                ]);
            }

            if ($includedDates->isEmpty()) {
                continue;
            }

            $comment = $this->commentFor($leave, (float) $includedDates->sum('amount'));

            foreach ($includedDates as $includedDate) {
                $entries = $entriesByUser->get($leave->user_id, collect());
                $entries->push([
                    'date' => $includedDate['date'],
                    'comment' => $comment,
                ]);
                $entriesByUser->put($leave->user_id, $entries);
            }
        }

        return $entriesByUser->map(
            fn (Collection $entries): Collection => $entries
                ->sortBy(fn (array $entry): string => $entry['date']->format('Y-m-d'))
                ->values()
        );
    }

    /**
     * @param  Collection<string, OfficeHoliday>  $holidays
     * @return Collection<int, array{date: Carbon, weight: float}>
     */
    protected function effectiveDates(LeaveRequest $leave, Collection $holidays): Collection
    {
        $dates = collect();
        $endDate = $leave->end_date ?? $leave->start_date;
        $isFiveDayWorkWeek = $this->leaveBalanceService->isFiveDayWorkWeekForUser($leave->user);

        foreach (CarbonPeriod::create($leave->start_date, $endDate) as $date) {
            if ($date->isSunday() || ($isFiveDayWorkWeek && $date->isSaturday())) {
                continue;
            }

            $weight = $date->isSaturday() ? 0.5 : 1.0;
            /** @var OfficeHoliday|null $holiday */
            $holiday = $holidays->get($date->toDateString());

            if ($holiday && ! $holiday->deducts_leave) {
                continue;
            }

            $dates->push([
                'date' => Carbon::instance($date)->copy(),
                'weight' => $weight,
            ]);
        }

        return $dates;
    }

    protected function netDeduction(LeaveRequest $leave): float
    {
        $netAmount = $leave->leaveBalanceTransactions->sum(
            fn (LeaveBalanceTransaction $transaction): float => match ($transaction->transaction_type) {
                LeaveBalanceTransaction::DEDUCT,
                LeaveBalanceTransaction::ADJUSTMENT => (float) $transaction->amount,
                LeaveBalanceTransaction::REFUND => -(float) $transaction->amount,
                default => 0.0,
            }
        );

        return max(0.0, round((float) $netAmount, 2));
    }

    protected function commentFor(LeaveRequest $leave, float $amount): string
    {
        $type = $leave->type instanceof LeaveType
            ? $leave->type->label()
            : (LeaveType::tryFrom((string) $leave->type)?->label() ?? (string) $leave->type);
        $status = $leave->status === LeaveRequest::PENDING_HR ? 'Menunggu HR' : 'Disetujui';
        $formattedAmount = rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');

        return implode("\n", [
            "Jenis: {$type}",
            "Status: {$status}",
            'Keterangan: '.($leave->reason ?: '-'),
            "Potong cuti: {$formattedAmount} hari",
        ]);
    }
}
