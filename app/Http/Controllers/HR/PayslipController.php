<?php

namespace App\Http\Controllers\Hr;

use App\Exports\PayslipTemplateExport;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\User;
use App\Models\Pt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Imports\PayslipPreviewImport;
use App\Models\EmployeeProfile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayslipPublishedMail;

class PayslipController extends Controller
{
    public function settings(Request $request)
    {
        Gate::authorize('manage-payroll');

        // Allow searching employees in settings
        $search = $request->input('search');

        $usersQuery = User::query()->active();
        if (!empty($search)) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->paginate(20)->appends(['search' => $search]);

        return view('hr.payroll.settings', compact('users', 'search'));
    }

    public function updateSettings(Request $request)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'can_manage_payroll' => 'required|boolean'
        ]);

        $user = User::query()->active()->find($request->user_id);
        if (!$user) {
            return redirect()->back()->with('error', 'Akses payroll hanya dapat diubah untuk karyawan berstatus ACTIVE.');
        }

        // Prevent removing permission from oneself if they are not HRD
        // Optional safety:
        if ($user->id === Auth::id() && !$request->can_manage_payroll && !$user->isHrManager()) {
            return redirect()->back()->with('error', 'Anda tidak bisa mencabut akses Anda sendiri.');
        }

        $user->can_manage_payroll = $request->can_manage_payroll;
        $user->save();

        return redirect()->back()->with('success', 'Hak akses Master Payroll berhasil diperbarui untuk ' . $user->name);
    }

    public function index(Request $request)
    {
        Gate::authorize('manage-payroll');

        $startMonth = $request->input('start_month', now()->month);
        $endMonth = $request->input('end_month', now()->month);
        $year = $request->input('year', now()->year);
        $ptId = $request->input('pt_id');
        $search = $request->input('search');

        // Swap if user inputs a reverse range
        if ($startMonth > $endMonth) {
            $temp = $startMonth;
            $startMonth = $endMonth;
            $endMonth = $temp;
        }

        // Ambil list PT untuk filter
        $pts = Pt::all();

        $payrollData = collect();

        $employees = User::query()
            ->active()
            ->when($ptId, function ($query) use ($ptId) {
                $query->whereHas('profile', function ($profileQuery) use ($ptId) {
                    $profileQuery->where('pt_id', $ptId);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->with(['profile', 'position', 'division', 'payslips' => function ($q) use ($startMonth, $endMonth, $year) {
                $q->whereBetween('period_month', [$startMonth, $endMonth])
                    ->where('period_year', $year);
            }])
            ->get();

        foreach ($employees as $employee) {
            for ($m = $startMonth; $m <= $endMonth; $m++) {
                $payslip = $employee->payslips->firstWhere('period_month', $m);

                $payrollData->push((object)[
                    'user' => $employee,
                    'month' => $m,
                    'year' => $year,
                    'payslip_status' => $payslip ? $payslip->status : 'BELUM_DIBUAT',
                    'latest_payslip' => $payslip
                ]);
            }
        }

        return view('hr.payroll.index', compact('payrollData', 'pts', 'startMonth', 'endMonth', 'year', 'ptId'));
    }

    public function exportExcel(Request $request)
    {
        Gate::authorize('manage-payroll');

        $startMonth = $request->input('start_month', now()->month);
        $endMonth = $request->input('end_month', now()->month);
        $year = $request->input('year', now()->year);
        $ptId = $request->input('pt_id');

        $ptName = 'ALL_PT';
        if ($ptId) {
            $pt = Pt::find($ptId);
            if ($pt) {
                // Remove spaces and non-alphanumeric characters for safe filename
                $ptName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $pt->name);
            }
        }

        $search = $request->input('search');

        $namePrefix = '';
        if (!empty($search)) {
            $userQuery = User::query()->active()->whereHas('profile', function ($q) use ($ptId) {
                if ($ptId) $q->where('pt_id', $ptId);
            })->where('name', 'like', '%' . $search . '%')->first();

            if ($userQuery) {
                $safeSearch = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $userQuery->name);
                $namePrefix = "{$safeSearch}_";
            } else {
                $safeSearch = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $search);
                $namePrefix = "{$safeSearch}_";
            }
        }

        $fileName = "{$namePrefix}{$ptName}_Slip_Gaji_{$startMonth}_to_{$endMonth}_{$year}.xlsx";

        return Excel::download(new PayslipTemplateExport($startMonth, $endMonth, $year, $ptId, $search), $fileName);
    }

    public function create(Request $request)
    {
        Gate::authorize('manage-payroll');

        $userId = $request->input('user_id') ?: $request->old('user_id');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        if (!$userId) {
            return redirect()->route('hr.payroll.index')->with('error', 'User ID is required to create a payslip.');
        }

        $user = User::query()->active()->find($userId);

        if (!$user) {
            return redirect()->route('hr.payroll.index')->with('error', 'User tidak ditemukan atau tidak berstatus ACTIVE.');
        }

        // Cek apakah sudah ada payslip
        $existingPayslip = Payslip::where('user_id', $userId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        if ($existingPayslip) {
            return redirect()->route('hr.payroll.index', [
                'start_month' => $month,
                'end_month' => $month,
                'year' => $year,
                'pt_id' => $request->input('filter_pt_id'),
            ])->with('warning', 'Slip gaji untuk periode ini sudah ada. Mengalihkan ke halaman edit.');
        }

        $pts = \App\Models\Pt::orderBy('name')->get();

        return view('hr.payroll.form', compact('user', 'month', 'year', 'pts'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020',
            'status' => 'required|in:DRAFT,PUBLISHED',
        ]);

        $data = $request->except(['_token', 'filter_start_month', 'filter_end_month', 'filter_year', 'filter_pt_id']);

        // Hitung Totals
        $totalPendapatan =
            ($data['gaji_pokok'] ?? 0) +
            ($data['tunjangan_jabatan'] ?? 0) +
            ($data['tunjangan_makan'] ?? 0) +
            ($data['fee_marketing'] ?? 0) +
            ($data['bonus_bulanan'] ?? 0) +
            ($data['tunjangan_telekomunikasi'] ?? 0) +
            ($data['tunjangan_lainnya'] ?? 0) +
            ($data['tunjangan_penempatan'] ?? 0) +
            ($data['tunjangan_asuransi'] ?? 0) +
            ($data['tunjangan_kelancaran'] ?? 0) +
            ($data['pendapatan_lain'] ?? 0) +
            ($data['tunjangan_transportasi'] ?? 0) +
            ($data['lembur'] ?? 0);

        $totalPotongan =
            ($data['potongan_bpjs_tk'] ?? 0) +
            ($data['potongan_pph21'] ?? 0) +
            ($data['potongan_hutang'] ?? 0) +
            ($data['potongan_bpjs_kes'] ?? 0) +
            ($data['potongan_terlambat'] ?? 0);

        $gajiBersih = $totalPendapatan - $totalPotongan;

        $data['total_pendapatan'] = $totalPendapatan;
        $data['total_potongan'] = $totalPotongan;
        $data['gaji_bersih'] = $gajiBersih;
        $data['sisa_utang'] = $this->normalizeSisaUtang($data['sisa_utang'] ?? null);
        $data['created_by'] = Auth::id();

        $activeUser = User::query()->active()->find($data['user_id']);
        if (!$activeUser) {
            return redirect()->route('hr.payroll.index', [
                'start_month' => $request->input('filter_start_month', $request->period_month),
                'end_month' => $request->input('filter_end_month', $request->period_month),
                'year' => $request->input('filter_year', $request->period_year),
                'pt_id' => $request->input('filter_pt_id'),
            ])->with('error', 'Slip gaji hanya dapat dibuat untuk karyawan dengan status ACTIVE.');
        }

        $payslip = Payslip::create($data);

        // Send email if status is PUBLISHED
        if ($payslip->status === 'PUBLISHED' && $payslip->user->email) {
            $ptName = $this->resolvePtNameByPayslipUserId($payslip->user_id);
            Mail::to($payslip->user->email)->send(new PayslipPublishedMail($payslip, $ptName));
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('filter_start_month', $request->period_month),
            'end_month' => $request->input('filter_end_month', $request->period_month),
            'year' => $request->input('filter_year', $request->period_year),
            'pt_id' => $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null),
        ])->with('success', 'Slip Gaji berhasil dibuat.' . ($payslip->status === 'PUBLISHED' ? ' Email notifikasi telah dikirim.' : ''));
    }

    public function edit(Payslip $payslip)
    {
        Gate::authorize('manage-payroll');

        $user = $payslip->user;
        $month = $payslip->period_month;
        $year = $payslip->period_year;

        $pts = \App\Models\Pt::orderBy('name')->get();

        return view('hr.payroll.form', compact('payslip', 'user', 'month', 'year', 'pts'));
    }

    public function update(Request $request, Payslip $payslip)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'status' => 'required|in:DRAFT,PUBLISHED',
        ]);

        $data = $request->except(['_token', '_method', 'filter_start_month', 'filter_end_month', 'filter_year', 'filter_pt_id']);

        $totalPendapatan =
            ($data['gaji_pokok'] ?? 0) +
            ($data['tunjangan_jabatan'] ?? 0) +
            ($data['tunjangan_makan'] ?? 0) +
            ($data['fee_marketing'] ?? 0) +
            ($data['bonus_bulanan'] ?? 0) +
            ($data['tunjangan_telekomunikasi'] ?? 0) +
            ($data['tunjangan_lainnya'] ?? 0) +
            ($data['tunjangan_penempatan'] ?? 0) +
            ($data['tunjangan_asuransi'] ?? 0) +
            ($data['tunjangan_kelancaran'] ?? 0) +
            ($data['pendapatan_lain'] ?? 0) +
            ($data['tunjangan_transportasi'] ?? 0) +
            ($data['lembur'] ?? 0);

        $totalPotongan =
            ($data['potongan_bpjs_tk'] ?? 0) +
            ($data['potongan_pph21'] ?? 0) +
            ($data['potongan_hutang'] ?? 0) +
            ($data['potongan_bpjs_kes'] ?? 0) +
            ($data['potongan_terlambat'] ?? 0);

        $gajiBersih = $totalPendapatan - $totalPotongan;

        $data['total_pendapatan'] = $totalPendapatan;
        $data['total_potongan'] = $totalPotongan;
        $data['gaji_bersih'] = $gajiBersih;
        $data['sisa_utang'] = $this->normalizeSisaUtang($data['sisa_utang'] ?? null);

        // Send email if status is PUBLISHED (allows re-sending if already published)
        $shouldSendEmail = ($data['status'] === 'PUBLISHED');

        $payslip->update($data);

        if ($shouldSendEmail && $payslip->user->email) {
            $ptName = $this->resolvePtNameByPayslipUserId($payslip->user_id);
            Mail::to($payslip->user->email)->send(new PayslipPublishedMail($payslip, $ptName));
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('filter_start_month', $payslip->period_month),
            'end_month' => $request->input('filter_end_month', $payslip->period_month),
            'year' => $request->input('filter_year', $payslip->period_year),
            'pt_id' => $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null),
        ])->with('success', 'Slip Gaji berhasil diperbarui.' . ($shouldSendEmail ? ' Email notifikasi telah dikirim.' : ''));
    }

    public function destroy(Request $request, Payslip $payslip)
    {
        Gate::authorize('manage-payroll');

        $month = $payslip->period_month;
        $year = $payslip->period_year;
        $ptId = $payslip->user->profile->pt_id ?? null;
        $employeeName = $payslip->user->name ?? 'Karyawan';

        $payslip->delete();

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('filter_start_month', $month),
            'end_month' => $request->input('filter_end_month', $month),
            'year' => $request->input('filter_year', $year),
            'pt_id' => $request->input('filter_pt_id', $ptId),
            'search' => $request->input('search'),
        ])->with('success', "Data slip gaji {$employeeName} berhasil dihapus.");
    }

    public function previewImport(Request $request)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,xlsm',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'pt_id' => 'required|exists:pts,id',
        ]);

        $file = $request->file('file');

        // INSTANSIASI CLASS IMPORT DAN AMBIL DATA DARI "KANTONG"
        $import = new PayslipPreviewImport();
        Excel::import($import, $file);

        // Ambil data yang sudah bersih dari properti mappedData
        $payslips = $import->mappedData;

        if (empty($payslips)) {
            return back()->with('error', 'Tidak ada data valid yang ditemukan dalam file. Pastikan NIK di baris ke-9 sesuai dengan database.');
        }

        $month = $request->input('month');
        $year = $request->input('year');
        $ptId = $request->input('pt_id');
        $pt = Pt::find($ptId);
        $pts = Pt::orderBy('name')->get();

        return view('hr.payroll.preview', compact('payslips', 'month', 'year', 'pt', 'pts'));
    }

    public function storeBulkImport(Request $request)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'payslips' => 'required|array',
            'month' => 'required|integer',
            'year' => 'required|integer',
            'pt_id' => 'nullable|integer',
            'action' => 'required|in:draft,publish',
        ]);

        $payslipsData = $request->input('payslips');
        $month = $request->input('month');
        $year = $request->input('year');
        $action = $request->input('action');

        // Jika action publish dan ada selected_rows, hanya proses yang dipilih
        $selectedRows = collect($request->input('selected_rows', []))
            ->filter(fn($v) => is_string($v) && preg_match('/^\d+-\d{1,2}-\d{4}$/', $v))
            ->unique()
            ->values();

        $status = ($action === 'publish') ? 'PUBLISHED' : 'DRAFT';
        $count = 0;
        $inactiveSkippedCount = 0;

        $activeUserIds = User::query()
            ->active()
            ->whereIn('id', collect($payslipsData)->pluck('user_id')->filter()->unique()->values())
            ->pluck('id')
            ->flip();

        foreach ($payslipsData as $data) {
            if (empty($data['user_id'])) continue;

            if (!$activeUserIds->has((int) $data['user_id'])) {
                $inactiveSkippedCount++;
                continue;
            }

            // Jika publish, skip baris yang tidak dipilih
            if ($action === 'publish' && $selectedRows->isNotEmpty()) {
                $rowKey = $data['user_id'] . '-' . ($data['period_month'] ?? $month) . '-' . ($data['period_year'] ?? $year);
                if (!$selectedRows->contains($rowKey)) {
                    continue;
                }
            }

            // Clean currency inputs
            $monetaryKeys = [
                'gaji_pokok',
                'tunjangan_jabatan',
                'tunjangan_makan',
                'fee_marketing',
                'bonus_bulanan',
                'tunjangan_telekomunikasi',
                'tunjangan_lainnya',
                'tunjangan_penempatan',
                'tunjangan_asuransi',
                'tunjangan_kelancaran',
                'pendapatan_lain',
                'tunjangan_transportasi',
                'lembur',
                'potongan_bpjs_tk',
                'potongan_pph21',
                'potongan_hutang',
                'potongan_bpjs_kes',
                'potongan_terlambat'
            ];

            $rowMonth = isset($data['period_month']) ? (int) $data['period_month'] : (int) $month;
            $rowYear = isset($data['period_year']) ? (int) $data['period_year'] : (int) $year;

            if ($rowMonth < 1 || $rowMonth > 12 || $rowYear < 2020) {
                continue;
            }

            foreach ($monetaryKeys as $key) {
                if (isset($data[$key])) {
                    $data[$key] = $this->cleanAndFormatCurrency($data[$key]);
                }
            }

            $totalPendapatan =
                ($data['gaji_pokok'] ?? 0) +
                ($data['tunjangan_jabatan'] ?? 0) +
                ($data['tunjangan_makan'] ?? 0) +
                ($data['fee_marketing'] ?? 0) +
                ($data['bonus_bulanan'] ?? 0) +
                ($data['tunjangan_telekomunikasi'] ?? 0) +
                ($data['tunjangan_lainnya'] ?? 0) +
                ($data['tunjangan_penempatan'] ?? 0) +
                ($data['tunjangan_asuransi'] ?? 0) +
                ($data['tunjangan_kelancaran'] ?? 0) +
                ($data['pendapatan_lain'] ?? 0) +
                ($data['tunjangan_transportasi'] ?? 0) +
                ($data['lembur'] ?? 0);

            $totalPotongan =
                ($data['potongan_bpjs_tk'] ?? 0) +
                ($data['potongan_pph21'] ?? 0) +
                ($data['potongan_hutang'] ?? 0) +
                ($data['potongan_bpjs_kes'] ?? 0) +
                ($data['potongan_terlambat'] ?? 0);

            $gajiBersih = $totalPendapatan - $totalPotongan;

            $updateData = [
                'gaji_pokok' => $data['gaji_pokok'] ?? 0,
                'tunjangan_jabatan' => $data['tunjangan_jabatan'] ?? 0,
                'tunjangan_makan' => $data['tunjangan_makan'] ?? 0,
                'fee_marketing' => $data['fee_marketing'] ?? 0,
                'bonus_bulanan' => $data['bonus_bulanan'] ?? 0,
                'tunjangan_telekomunikasi' => $data['tunjangan_telekomunikasi'] ?? 0,
                'tunjangan_lainnya' => $data['tunjangan_lainnya'] ?? 0,
                'tunjangan_penempatan' => $data['tunjangan_penempatan'] ?? 0,
                'tunjangan_asuransi' => $data['tunjangan_asuransi'] ?? 0,
                'tunjangan_kelancaran' => $data['tunjangan_kelancaran'] ?? 0,
                'pendapatan_lain' => $data['pendapatan_lain'] ?? 0,
                'tunjangan_transportasi' => $data['tunjangan_transportasi'] ?? 0,
                'lembur' => $data['lembur'] ?? 0,
                'potongan_bpjs_tk' => $data['potongan_bpjs_tk'] ?? 0,
                'potongan_pph21' => $data['potongan_pph21'] ?? 0,
                'potongan_hutang' => $data['potongan_hutang'] ?? 0,
                'potongan_bpjs_kes' => $data['potongan_bpjs_kes'] ?? 0,
                'potongan_terlambat' => $data['potongan_terlambat'] ?? 0,

                'sisa_utang' => $this->normalizeSisaUtang($data['sisa_utang'] ?? null),

                'total_pendapatan' => $totalPendapatan,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $gajiBersih,

                'status' => $status,
                'created_by' => Auth::id(),
            ];

            $payslip = Payslip::updateOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'period_month' => $rowMonth,
                    'period_year' => $rowYear,
                ],
                $updateData
            );

            // Cek apakah email perlu dikirim (Blasting email ke semua jika action Publish)
            $shouldSendEmail = ($status === 'PUBLISHED');

            if ($shouldSendEmail && $payslip->user && $payslip->user->email) {
                $ptName = $this->resolvePtNameByPayslipUserId($payslip->user_id);
                Mail::to($payslip->user->email)->send(new PayslipPublishedMail($payslip, $ptName));
            }

            $count++;
        }

        $message = "Berhasil mengimpor $count data slip gaji ($status).";
        if ($inactiveSkippedCount > 0) {
            $message .= " {$inactiveSkippedCount} data dilewati karena karyawan tidak berstatus ACTIVE.";
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('start_month', $month),
            'end_month' => $request->input('end_month', $month),
            'year' => $year,
            'pt_id' => $request->input('pt_id'),
        ])->with('success', $message);
    }

    public function sendSelectedEmails(Request $request)
    {
        Gate::authorize('manage-payroll');

        $selectedRows = collect($request->input('selected_rows', []))
            ->filter(function ($value) {
                return is_string($value) && preg_match('/^\d+-\d{1,2}-\d{4}$/', $value);
            })
            ->unique()
            ->values();

        if ($selectedRows->isEmpty()) {
            return redirect()->back()->with('error', 'Pilih minimal satu data karyawan untuk kirim email.');
        }

        $sentCount = 0;
        $draftPublishedCount = 0;
        $missingPayslipCount = 0;
        $missingEmailCount = 0;
        $inactiveUserCount = 0;

        foreach ($selectedRows as $rowKey) {
            [$userId, $month, $year] = array_map('intval', explode('-', $rowKey));

            $payslip = Payslip::where('user_id', $userId)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->first();

            if (!$payslip) {
                $missingPayslipCount++;
                continue;
            }

            if (!$payslip->user || $payslip->user->status !== User::STATUS_ACTIVE) {
                $inactiveUserCount++;
                continue;
            }

            if ($payslip->status === 'DRAFT') {
                $payslip->status = 'PUBLISHED';
                $payslip->save();
                $draftPublishedCount++;
            }

            if (empty($payslip->user->email)) {
                $missingEmailCount++;
                continue;
            }

            $ptName = $this->resolvePtNameByPayslipUserId($payslip->user_id);
            Mail::to($payslip->user->email)->send(new PayslipPublishedMail($payslip, $ptName));
            $sentCount++;
        }

        $messages = ["{$sentCount} email berhasil diproses."];

        if ($draftPublishedCount > 0) {
            $messages[] = "{$draftPublishedCount} slip DRAFT diubah menjadi PUBLISHED.";
        }
        if ($missingPayslipCount > 0) {
            $messages[] = "{$missingPayslipCount} data dilewati karena slip belum dibuat.";
        }
        if ($missingEmailCount > 0) {
            $messages[] = "{$missingEmailCount} data dilewati karena email karyawan kosong.";
        }
        if ($inactiveUserCount > 0) {
            $messages[] = "{$inactiveUserCount} data dilewati karena karyawan tidak berstatus ACTIVE.";
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('start_month', now()->month),
            'end_month' => $request->input('end_month', now()->month),
            'year' => $request->input('year', now()->year),
            'pt_id' => $request->input('pt_id'),
            'search' => $request->input('search'),
        ])->with($sentCount > 0 ? 'success' : 'warning', implode(' ', $messages));
    }

    private function resolvePtNameByPayslipUserId(int $userId): ?string
    {
        $profile = EmployeeProfile::with('pt:id,name')
            ->where('user_id', $userId)
            ->first();

        return $profile?->pt?->name;
    }

    private function cleanAndFormatCurrency($value)
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (empty($value)) {
            return 0;
        }

        // Convert to string just in case
        $value = (string) $value;

        // Assume Indonesian formatting: 1.000.000,00
        $value = preg_replace('/[Rp\s]/', '', $value); // Remove Rp and spaces
        $value = str_replace('.', '', $value); // Remove thousands separator (dot)
        $value = str_replace(',', '.', $value); // Replace decimal separator (comma) with dot

        return is_numeric($value) ? (float) $value : 0;
    }

    private function normalizeSisaUtang($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/', '', $text);
        $normalized = str_ireplace('rp', '', $normalized);

        if (preg_match('/^0+([.,]0+)?$/', $normalized)) {
            return null;
        }

        return $text;
    }
}
