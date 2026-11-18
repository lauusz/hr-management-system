<?php

namespace App\Http\Controllers\Hr;

use App\Exports\PayslipTemplateExport;
use App\Jobs\SendPayslipEmailJob;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\User;
use App\Models\Division;
use App\Models\Pt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Imports\PayslipPreviewImport;
use App\Models\EmployeeProfile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        $divisionId = $request->input('division_id');

        // Swap if user inputs a reverse range
        if ($startMonth > $endMonth) {
            $temp = $startMonth;
            $startMonth = $endMonth;
            $endMonth = $temp;
        }

        // Ambil list PT dan Divisi untuk filter
        $pts = Pt::all();
        $divisions = Division::orderBy('name')->get();

        $payrollData = collect();

        // Muatan awal hanya mengirim kerangka halaman; data payroll diambil lewat request AJAX
        // (X-Requested-With) ke URL yang sama. ?full=1 = fallback server-rendered tanpa JavaScript.
        $deferred = ! $request->ajax() && ! $request->boolean('full');
        if ($deferred) {
            return view('hr.payroll.index', compact('payrollData', 'pts', 'startMonth', 'endMonth', 'year', 'ptId', 'divisions', 'divisionId', 'deferred'));
        }

        $employees = User::query()
            ->active()
            ->when($ptId, function ($query) use ($ptId) {
                $query->whereHas('profile', function ($profileQuery) use ($ptId) {
                    $profileQuery->where('pt_id', $ptId);
                });
            })
            ->when($divisionId, function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
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

        return view('hr.payroll.index', compact('payrollData', 'pts', 'startMonth', 'endMonth', 'year', 'ptId', 'divisions', 'divisionId', 'deferred'));
    }

    public function exportExcel(Request $request)
    {
        Gate::authorize('manage-payroll');

        $startMonth = $request->input('start_month', now()->month);
        $endMonth = $request->input('end_month', now()->month);
        $year = $request->input('year', now()->year);
        $ptId = $request->input('pt_id');
        $divisionId = $request->input('division_id');

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

        return Excel::download(new PayslipTemplateExport($startMonth, $endMonth, $year, $ptId, $search, $divisionId), $fileName);
    }

    public function create(Request $request)
    {
        Gate::authorize('manage-payroll');

        $userId = $request->input('user_id') ?: $request->old('user_id');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        if (!$userId) {
            return redirect()->route('hr.payroll.index')->with('error', 'Karyawan wajib dipilih untuk membuat slip gaji.');
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
                'division_id' => $request->input('filter_division_id'),
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
            ($data['lembur'] ?? 0) +
            ($data['thr'] ?? 0) +
            ($data['bonus'] ?? 0);

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
                'division_id' => $request->input('filter_division_id'),
            ])->with('error', 'Slip gaji hanya dapat dibuat untuk karyawan dengan status ACTIVE.');
        }

        $payslip = Payslip::create($data);

        // Send email if status is PUBLISHED
        $queued = false;
        if ($payslip->status === 'PUBLISHED' && $payslip->user->email) {
            $queued = $this->dispatchPayslipEmailJob($payslip);
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('filter_start_month', $request->period_month),
            'end_month' => $request->input('filter_end_month', $request->period_month),
            'year' => $request->input('filter_year', $request->period_year),
            'pt_id' => $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null),
            'division_id' => $request->input('filter_division_id'),
        ])->with('success', 'Slip Gaji berhasil dibuat.' . ($queued ? ' Email notifikasi dijadwalkan ke queue.' : ($payslip->status === 'PUBLISHED' ? ' Email tidak dijadwalkan (email kosong atau antrean gagal).' : '')));
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
            ($data['lembur'] ?? 0) +
            ($data['thr'] ?? 0) +
            ($data['bonus'] ?? 0);

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

        $queued = false;
        if ($shouldSendEmail && $payslip->user->email) {
            $queued = $this->dispatchPayslipEmailJob($payslip);
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('filter_start_month', $payslip->period_month),
            'end_month' => $request->input('filter_end_month', $payslip->period_month),
            'year' => $request->input('filter_year', $payslip->period_year),
            'pt_id' => $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null),
            'division_id' => $request->input('filter_division_id'),
        ])->with('success', 'Slip Gaji berhasil diperbarui.' . ($queued ? ' Email notifikasi dijadwalkan ke queue.' : ($shouldSendEmail ? ' Email tidak dijadwalkan (email kosong atau antrean gagal).' : '')));
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
            'division_id' => $request->input('filter_division_id'),
            'search' => $request->input('search'),
        ])->with('success', "Data slip gaji {$employeeName} berhasil dihapus.");
    }

    public function destroySelected(Request $request)
    {
        Gate::authorize('manage-payroll');

        $selectedRows = collect($request->input('selected_rows', []))
            ->filter(fn($value) => is_string($value) && preg_match('/^\d+-\d{1,2}-\d{4}$/', $value))
            ->unique()
            ->values();

        if ($selectedRows->isEmpty()) {
            return redirect()->route('hr.payroll.index', [
                'start_month' => $request->input('filter_start_month', now()->month),
                'end_month' => $request->input('filter_end_month', now()->month),
                'year' => $request->input('filter_year', now()->year),
                'pt_id' => $request->input('filter_pt_id'),
                'division_id' => $request->input('filter_division_id'),
                'search' => $request->input('search'),
            ])->with('error', 'Pilih minimal satu data payroll yang akan dihapus.');
        }

        $deletedCount = 0;
        $missingCount = 0;

        foreach ($selectedRows as $rowKey) {
            [$userId, $month, $year] = array_map('intval', explode('-', $rowKey));

            $deleted = Payslip::where('user_id', $userId)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->delete();

            if ($deleted > 0) {
                $deletedCount += $deleted;
            } else {
                $missingCount++;
            }
        }

        $flashType = $deletedCount > 0 ? 'success' : 'warning';
        $message = "{$deletedCount} data slip gaji berhasil dihapus.";

        if ($missingCount > 0) {
            $message .= " {$missingCount} data dilewati karena slip gaji tidak ditemukan.";
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('filter_start_month', now()->month),
            'end_month' => $request->input('filter_end_month', now()->month),
            'year' => $request->input('filter_year', now()->year),
            'pt_id' => $request->input('filter_pt_id'),
            'division_id' => $request->input('filter_division_id'),
            'search' => $request->input('search'),
        ])->with($flashType, $message);
    }

    public function previewImport(Request $request)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,xlsm',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'pt_id' => 'nullable|exists:pts,id',
        ]);

        try {
            $file = $request->file('file');

            // INSTANSIASI CLASS IMPORT DAN AMBIL DATA DARI "KANTONG"
            $import = new PayslipPreviewImport();
            Excel::import($import, $file);

            // Ambil data yang sudah bersih dari properti mappedData
            $payslips = $import->mappedData;
            $unmatchedEmployees = $import->unmatchedRows;

            if (empty($payslips)) {
                $errorMessage = 'Tidak ada data valid yang ditemukan dalam file. Pastikan nama atau NIK karyawan sesuai database.';

                if (!empty($unmatchedEmployees)) {
                    $names = collect($unmatchedEmployees)
                        ->pluck('name')
                        ->filter()
                        ->unique()
                        ->take(5)
                        ->implode(', ');

                    if ($names !== '') {
                        $errorMessage .= ' Nama yang tidak ditemukan: ' . $names . '.';
                    }
                }

                return back()->with('error', $errorMessage);
            }

            $month = $request->input('month');
            $year = $request->input('year');
            $ptId = $request->input('pt_id');
            $pt = Pt::find($ptId);
            $pts = Pt::orderBy('name')->get();

            return view('hr.payroll.preview', compact('payslips', 'month', 'year', 'pt', 'pts', 'unmatchedEmployees'));
        } catch (Throwable $e) {
            Log::error('Payroll preview import failed', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memproses file import. Silakan cek format file atau hubungi tim IT.');
        }
    }

    /** Batas jumlah target per operasi simpan/publish dari Master Payroll atau Preview Import. */
    private const MAX_BULK_TARGETS = 2000;

    private const TARGET_KEY_PATTERN = '/^(\d+)-(\d{1,2})-(\d{4})$/';

    /**
     * Simpan DRAFT / Publish massal.
     *
     * - source=master  : target = pilihan eksplisit (targets_json, payload lengkap per target).
     * - source=preview : seluruh baris hasil preview yang dikonfirmasi (payslips[...]), perilaku existing.
     *
     * Seluruh batch divalidasi dulu, ditulis dalam satu transaksi, lalu email dijadwalkan SETELAH commit.
     */
    public function storeBulkImport(Request $request)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'source' => 'required|in:master,preview',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'pt_id' => 'nullable|integer',
            'action' => 'required|in:draft,publish',
            'thr_only_mode' => 'nullable|boolean',
            'expected_rows' => 'nullable|integer|min:1',
            'operation_id' => 'nullable|uuid',
            'targets_json' => 'required_if:source,master|nullable|string|max:8388608',
            'payslips' => 'required_if:source,preview|nullable|array',
        ]);

        $source = $request->input('source');
        $month = (int) $request->input('month');
        $year = (int) $request->input('year');
        $action = $request->input('action');
        $status = $action === 'publish' ? 'PUBLISHED' : 'DRAFT';
        $thrOnlyMode = $request->boolean('thr_only_mode') && $action === 'publish';
        $operationId = $request->input('operation_id');

        $redirectParams = [
            'start_month' => $request->input('start_month', $month),
            'end_month' => $request->input('end_month', $month),
            'year' => $year,
            'pt_id' => $request->input('pt_id'),
            'division_id' => $request->input('division_id'),
            'search' => $request->input('search'),
        ];

        if ($status === 'PUBLISHED' && !$operationId) {
            return back()->with('error', 'Identitas operasi publish tidak ditemukan. Muat ulang halaman lalu ulangi.');
        }

        // ---- 1. Normalisasi + validasi seluruh target SEBELUM menulis apa pun ----
        if ($source === 'master') {
            $decoded = json_decode((string) $request->input('targets_json'), true, 8);
            $rawTargets = is_array($decoded) && array_is_list($decoded) ? $decoded : null;
            if ($rawTargets === null || $rawTargets === []) {
                return back()->with('error', 'Pilih minimal satu baris payroll sebagai target. Tidak ada data yang diproses.');
            }
        } else {
            $rawTargets = array_values((array) $request->input('payslips', []));
            $expectedRows = (int) $request->input('expected_rows', 0);
            if ($expectedRows > 0 && count($rawTargets) < $expectedRows) {
                return back()->with('error', 'Jumlah data yang diterima server tidak lengkap. Kemungkinan batas max_input_vars di server terlalu kecil. Hubungi tim IT untuk menaikkan max_input_vars.');
            }
        }

        if (count($rawTargets) > self::MAX_BULK_TARGETS) {
            return back()->with('error', 'Jumlah target melebihi batas ' . self::MAX_BULK_TARGETS . ' baris per operasi. Tidak ada data yang diproses.');
        }

        [$targets, $errors] = $this->normalizeBulkTargets($rawTargets, $source, $month, $year);
        if ($errors !== []) {
            return back()->with('error', 'Data ditolak, tidak ada yang disimpan atau dikirim: ' . implode(' ', array_slice($errors, 0, 5)));
        }

        $activeUserIds = User::query()
            ->active()
            ->whereIn('id', collect($targets)->pluck('user_id')->unique()->values())
            ->pluck('id')
            ->flip();

        // ---- 2. Klaim operasi (cegah pengulangan request yang sama) ----
        $claimKey = null;
        if ($status === 'PUBLISHED') {
            $claimKey = 'payroll:operation:' . $operationId;
            if (!Cache::add($claimKey, Auth::id(), now()->addHours(6))) {
                return redirect()->route('hr.payroll.index', $redirectParams)
                    ->with('warning', 'Operasi publish ini sudah diproses atau sedang berjalan. Tidak ada email tambahan yang dijadwalkan. Muat ulang halaman untuk operasi baru.');
            }
        }

        // ---- 3. Simpan atomik ----
        $inactiveSkippedCount = 0;
        $toNotify = [];

        try {
            DB::transaction(function () use ($targets, $activeUserIds, $status, &$inactiveSkippedCount, &$toNotify) {
                foreach ($targets as $target) {
                    if (!$activeUserIds->has($target['user_id'])) {
                        $inactiveSkippedCount++;
                        continue;
                    }

                    $identity = [
                        'user_id' => $target['user_id'],
                        'period_month' => $target['period_month'],
                        'period_year' => $target['period_year'],
                    ];

                    $values = $target['values'];
                    $totalPendapatan = array_sum(array_intersect_key($values, array_flip(array_slice(Payslip::NOMINAL_FIELDS, 0, 15))));
                    $totalPotongan = array_sum(array_intersect_key($values, array_flip(array_slice(Payslip::NOMINAL_FIELDS, 15))));

                    $updateData = $values + [
                        'sisa_utang' => $this->normalizeSisaUtang($target['sisa_utang']),
                        'total_pendapatan' => $totalPendapatan,
                        'total_potongan' => $totalPotongan,
                        'gaji_bersih' => $totalPendapatan - $totalPotongan,
                        'status' => $status,
                        'created_by' => Auth::id(),
                    ];

                    $payslip = Payslip::where($identity)->lockForUpdate()->first();
                    if ($payslip) {
                        $payslip->update($updateData);
                    } else {
                        $payslip = Payslip::create($identity + $updateData);
                    }

                    if ($status === 'PUBLISHED') {
                        $toNotify[] = $payslip;
                    }
                }
            });
        } catch (Throwable $e) {
            if ($claimKey) {
                Cache::forget($claimKey); // tidak ada yang tersimpan: operasi boleh diulang
            }

            Log::error('Payroll bulk import failed', [
                'user_id' => Auth::id(),
                'source' => $source,
                'action' => $action,
                'targets_count' => count($targets),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menyimpan data payroll. Tidak ada data yang tersimpan. Detail error sudah dicatat di log server.');
        }

        $savedCount = count($targets) - $inactiveSkippedCount;

        // ---- 4. Jadwalkan email SETELAH commit ----
        $scheduled = 0;
        $noEmail = 0;
        $enqueueFailed = 0;
        $emailDelaySeconds = 0;

        foreach ($toNotify as $payslip) {
            $payslip->loadMissing('user');
            if (!$payslip->user || trim((string) $payslip->user->email) === '') {
                $noEmail++;
                continue;
            }

            if ($this->dispatchPayslipEmailJob($payslip, $thrOnlyMode, $emailDelaySeconds, $operationId)) {
                $scheduled++;
            } else {
                $enqueueFailed++;
            }
        }

        $message = "Berhasil menyimpan $savedCount data slip gaji ($status).";
        if ($status === 'PUBLISHED') {
            $message .= " $scheduled email dijadwalkan ke queue dengan jeda acak 10-15 detik per email (dijadwalkan, belum tentu terkirim).";
        }
        if ($noEmail > 0) {
            $message .= " $noEmail data dilewati pengirimannya karena email karyawan kosong.";
        }
        if ($inactiveSkippedCount > 0) {
            $message .= " {$inactiveSkippedCount} data dilewati karena karyawan tidak berstatus ACTIVE.";
        }
        if ($enqueueFailed > 0) {
            $message .= " PERHATIAN: {$enqueueFailed} email gagal dimasukkan ke antrean (data sudah tersimpan); kirim ulang slip tersebut.";
        }

        return redirect()->route('hr.payroll.index', $redirectParams)
            ->with($enqueueFailed > 0 ? 'warning' : 'success', $message);
    }

    /**
     * Normalisasi + validasi target bulk. Mengembalikan [targets, errors]; bila ada error, tidak ada yang ditulis.
     * Nominal yang hilang atau tidak valid ditolak (tidak diam-diam menjadi 0).
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>}
     */
    private function normalizeBulkTargets(array $rawTargets, string $source, int $defaultMonth, int $defaultYear): array
    {
        $targets = [];
        $errors = [];
        $seen = [];

        foreach ($rawTargets as $i => $row) {
            $label = 'Baris ke-' . ($i + 1);

            if (!is_array($row)) {
                $errors[] = "$label tidak valid.";
                continue;
            }

            $userId = $row['user_id'] ?? null;
            if (!is_scalar($userId) || !ctype_digit((string) $userId) || (int) $userId < 1) {
                $errors[] = "$label: identitas karyawan tidak valid.";
                continue;
            }

            if ($source === 'master') {
                $month = $row['period_month'] ?? null;
                $year = $row['period_year'] ?? null;
            } else {
                $month = $row['period_month'] ?? $defaultMonth;
                $year = $row['period_year'] ?? $defaultYear;
            }

            if (!is_scalar($month) || !ctype_digit((string) $month) || (int) $month < 1 || (int) $month > 12
                || !is_scalar($year) || !ctype_digit((string) $year) || (int) $year < 2020 || (int) $year > 2100) {
                $errors[] = "$label: periode tidak valid.";
                continue;
            }

            $key = (int) $userId . '-' . (int) $month . '-' . (int) $year;
            if (isset($seen[$key])) {
                $errors[] = "$label: target ganda ($key).";
                continue;
            }
            $seen[$key] = true;

            $values = [];
            $rowOk = true;
            foreach (Payslip::NOMINAL_FIELDS as $field) {
                if (!array_key_exists($field, $row)) {
                    $errors[] = "$label ($key): field $field tidak lengkap.";
                    $rowOk = false;
                    continue;
                }
                $parsed = $this->parseNominal($row[$field]);
                if ($parsed === null) {
                    $errors[] = "$label ($key): nominal $field tidak valid.";
                    $rowOk = false;
                    continue;
                }
                $values[$field] = $parsed;
            }

            if (array_key_exists('sisa_utang', $row) === false && $source === 'master') {
                $errors[] = "$label ($key): field sisa_utang tidak lengkap.";
                $rowOk = false;
            }
            $sisaUtang = $row['sisa_utang'] ?? null;
            if ($sisaUtang !== null && (!is_scalar($sisaUtang) || mb_strlen((string) $sisaUtang) > 255)) {
                $errors[] = "$label ($key): sisa_utang tidak valid.";
                $rowOk = false;
            }

            if (!$rowOk) {
                continue;
            }

            $targets[] = [
                'user_id' => (int) $userId,
                'period_month' => (int) $month,
                'period_year' => (int) $year,
                'values' => $values,
                'sisa_utang' => $sisaUtang === null ? null : (string) $sisaUtang,
            ];
        }

        return [$targets, $errors];
    }

    /**
     * Format yang diterima: angka biasa, atau format Indonesia (titik ribuan, koma desimal maks. 2 digit),
     * boleh diawali tanda minus dan "Rp". String kosong / format lain ditolak (mengembalikan null).
     */
    private function parseNominal(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return is_finite((float) $value) && abs((float) $value) < 1e13 ? round((float) $value, 2) : null;
        }

        if (!is_string($value)) {
            return null;
        }

        $text = preg_replace('/\s+/', '', $value);
        $text = preg_replace('/^(-?)rp/i', '$1', $text);

        if (!preg_match('/^-?(\d{1,3}(\.\d{3})+|\d+)(,\d{1,2})?$/', $text)) {
            return null;
        }

        $number = (float) str_replace(',', '.', str_replace('.', '', $text));

        return abs($number) < 1e13 ? $number : null;
    }

    /**
     * Jadwalkan email untuk baris terpilih. Setiap permintaan butuh operation_id baru; permintaan dengan
     * operation_id yang sama tidak menjadwalkan ulang. Kirim ulang yang disengaja = permintaan baru (id baru).
     * Catatan: tidak ada tombol UI yang memanggil route ini saat ini (hasil penelusuran view).
     */
    public function sendSelectedEmails(Request $request)
    {
        Gate::authorize('manage-payroll');

        $request->validate(['operation_id' => 'required|uuid']);

        $thrOnlyMode = $request->boolean('thr_only_mode');
        $operationId = $request->input('operation_id');

        $selectedRows = collect($request->input('selected_rows', []))
            ->filter(function ($value) {
                return is_string($value) && preg_match(self::TARGET_KEY_PATTERN, $value);
            })
            ->unique()
            ->values();

        if ($selectedRows->isEmpty()) {
            return redirect()->back()->with('error', 'Pilih minimal satu data karyawan untuk kirim email.');
        }

        if ($selectedRows->count() > self::MAX_BULK_TARGETS) {
            return redirect()->back()->with('error', 'Jumlah target melebihi batas ' . self::MAX_BULK_TARGETS . ' baris per operasi.');
        }

        $claimKey = 'payroll:operation:' . $operationId;
        if (!Cache::add($claimKey, Auth::id(), now()->addHours(6))) {
            return redirect()->back()->with('warning', 'Operasi kirim email ini sudah diproses. Tidak ada email tambahan yang dijadwalkan.');
        }

        $sentCount = 0;
        $draftPublishedCount = 0;
        $missingPayslipCount = 0;
        $missingEmailCount = 0;
        $inactiveUserCount = 0;
        $enqueueFailed = 0;
        $emailDelaySeconds = 0;
        $toDispatch = [];

        try {
            DB::transaction(function () use ($selectedRows, &$draftPublishedCount, &$missingPayslipCount, &$missingEmailCount, &$inactiveUserCount, &$toDispatch) {
                foreach ($selectedRows as $rowKey) {
                    [$userId, $month, $year] = array_map('intval', explode('-', $rowKey));

                    $payslip = Payslip::where('user_id', $userId)
                        ->where('period_month', $month)
                        ->where('period_year', $year)
                        ->lockForUpdate()
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

                    $toDispatch[] = $payslip;
                }
            });
        } catch (Throwable $e) {
            Cache::forget($claimKey);
            Log::error('Payroll send selected emails failed', ['user_id' => Auth::id(), 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Terjadi kesalahan. Tidak ada email yang dijadwalkan.');
        }

        foreach ($toDispatch as $payslip) {
            if ($this->dispatchPayslipEmailJob($payslip, $thrOnlyMode, $emailDelaySeconds, $operationId)) {
                $sentCount++;
            } else {
                $enqueueFailed++;
            }
        }

        $messages = ["{$sentCount} email dijadwalkan ke queue dengan jeda acak 10-15 detik per email."];

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
        if ($enqueueFailed > 0) {
            $messages[] = "PERHATIAN: {$enqueueFailed} email gagal dimasukkan ke antrean.";
        }

        return redirect()->route('hr.payroll.index', [
            'start_month' => $request->input('start_month', now()->month),
            'end_month' => $request->input('end_month', now()->month),
            'year' => $request->input('year', now()->year),
            'pt_id' => $request->input('pt_id'),
            'division_id' => $request->input('division_id'),
            'search' => $request->input('search'),
        ])->with($sentCount > 0 && $enqueueFailed === 0 ? 'success' : 'warning', implode(' ', $messages));
    }

    private function resolvePtNameByPayslipUserId(int $userId): ?string
    {
        $profile = EmployeeProfile::with('pt:id,name')
            ->where('user_id', $userId)
            ->first();

        return $profile?->pt?->name;
    }

    /**
     * Memasukkan satu job email ke antrean. Mengembalikan false bila gagal masuk antrean (data tetap tersimpan).
     * Job membawa fingerprint isi slip supaya job lama tidak mengirim versi yang sudah berubah.
     */
    private function dispatchPayslipEmailJob(Payslip $payslip, bool $thrOnly = false, ?int &$delaySeconds = null, ?string $operationId = null): bool
    {
        $scheduledDelaySeconds = 0;

        if ($delaySeconds !== null) {
            $delaySeconds += random_int(10, 15);
            $scheduledDelaySeconds = $delaySeconds;
        }

        $employeeName = $payslip->user->name ?? '-';
        $email = trim($payslip->user->email ?? '');

        try {
            $pendingDispatch = SendPayslipEmailJob::dispatch(
                $payslip->id,
                $employeeName,
                $email,
                $this->resolvePtNameByPayslipUserId($payslip->user_id),
                $thrOnly,
                $scheduledDelaySeconds,
                $payslip->revisionFingerprint(),
                $operationId,
            )->onQueue('emails')->afterCommit();

            if ($scheduledDelaySeconds > 0) {
                $pendingDispatch->delay(Carbon::now()->addSeconds($scheduledDelaySeconds));
            }

            // PendingDispatch mengantre saat objeknya dilepas; lepaskan di sini agar kegagalan tertangkap.
            unset($pendingDispatch);
        } catch (Throwable $e) {
            Log::error('Payroll email enqueue failed', [
                'payslip_id' => $payslip->id,
                'operation_id' => $operationId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        SendPayslipEmailJob::logScheduled($employeeName, $email, $scheduledDelaySeconds, $operationId);

        return true;
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
