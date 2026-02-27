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

        $usersQuery = User::query();
        if (!empty($search)) {
            $usersQuery->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
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

        $user = User::findOrFail($request->user_id);

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

        // Jika tidak ada PT yang dipilih, default ke PT pertama (jika ada)
        if (!$ptId && $pts->isNotEmpty()) {
            $ptId = $pts->first()->id;
        }

        $payrollData = collect();

        if ($ptId) {
            $employees = User::whereHas('profile', function ($query) use ($ptId) {
                $query->where('pt_id', $ptId);
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
            $userQuery = User::whereHas('profile', function ($q) use ($ptId) {
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

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('hr.payroll.index')->with('error', 'User not found.');
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
            ($data['tunjangan_telekomunikasi'] ?? 0) +
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
        $data['created_by'] = Auth::id();

        $payslip = Payslip::create($data);

        // Send email if status is PUBLISHED
        if ($payslip->status === 'PUBLISHED' && $payslip->user->email) {
            $ptId = $request->input('pt_id', $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null));
            $ptName = \App\Models\Pt::find($ptId)->name ?? null;
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
            ($data['tunjangan_telekomunikasi'] ?? 0) +
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

        // Send email if status is PUBLISHED (allows re-sending if already published)
        $shouldSendEmail = ($data['status'] === 'PUBLISHED');

        $payslip->update($data);

        if ($shouldSendEmail && $payslip->user->email) {
            $ptId = $request->input('pt_id', $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null));
            $ptName = \App\Models\Pt::find($ptId)->name ?? null;
            Mail::to($payslip->user->email)->send(new PayslipPublishedMail($payslip, $ptName));
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('filter_start_month', $payslip->period_month),
            'end_month' => $request->input('filter_end_month', $payslip->period_month),
            'year' => $request->input('filter_year', $payslip->period_year),
            'pt_id' => $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null),
        ])->with('success', 'Slip Gaji berhasil diperbarui.' . ($shouldSendEmail ? ' Email notifikasi telah dikirim.' : ''));
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
            'pt_id' => 'required|integer',
            'action' => 'required|in:draft,publish',
        ]);

        $payslipsData = $request->input('payslips');
        $month = $request->input('month');
        $year = $request->input('year');
        $action = $request->input('action');

        $status = ($action === 'publish') ? 'PUBLISHED' : 'DRAFT';
        $count = 0;

        foreach ($payslipsData as $data) {
            if (empty($data['user_id'])) continue;

            // Clean currency inputs
            $monetaryKeys = [
                'gaji_pokok',
                'tunjangan_jabatan',
                'tunjangan_makan',
                'fee_marketing',
                'tunjangan_telekomunikasi',
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
                'potongan_terlambat',
                'sisa_utang'
            ];

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
                ($data['tunjangan_telekomunikasi'] ?? 0) +
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
                'tunjangan_telekomunikasi' => $data['tunjangan_telekomunikasi'] ?? 0,
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

                'sisa_utang' => $data['sisa_utang'] ?? 0,

                'total_pendapatan' => $totalPendapatan,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $gajiBersih,

                'status' => $status,
                'created_by' => Auth::id(),
            ];

            $payslip = Payslip::updateOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'period_month' => $month,
                    'period_year' => $year,
                ],
                $updateData
            );

            // Cek apakah email perlu dikirim (Blasting email ke semua jika action Publish)
            $shouldSendEmail = ($status === 'PUBLISHED');

            if ($shouldSendEmail && $payslip->user && $payslip->user->email) {
                $ptId = $request->input('pt_id');
                $ptName = \App\Models\Pt::find($ptId)->name ?? null;
                Mail::to($payslip->user->email)->send(new PayslipPublishedMail($payslip, $ptName));
            }

            $count++;
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $month,
            'end_month' => $month,
            'year' => $year,
            'pt_id' => $request->input('pt_id'),
        ])->with('success', "Berhasil mengimpor $count data slip gaji ($status).");
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
}
