<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\User;
use App\Models\Pt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PayslipController extends Controller
{
    // Pastikan hanya user yang berhak yang bisa akses
    // Bisa juga via middleware di route

    public function index(Request $request)
    {
        Gate::authorize('manage-payroll');

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $ptId = $request->input('pt_id');

        // Ambil list PT untuk filter
        $pts = Pt::all();

        // Jika tidak ada PT yang dipilih, default ke PT pertama (jika ada)
        if (!$ptId && $pts->isNotEmpty()) {
            $ptId = $pts->first()->id;
        }

        $employees = collect();

        if ($ptId) {
            // Ambil karyawan yang bekerja di PT tersebut
            // Asumsi: Ada relasi di User/EmployeeProfile ke PT
            // User -> EmployeeProfile -> Pt (via pt_id di profile, atau logic lain)
            // Cek model User: public function pt() defined via hasOneThrough

            $employees = User::whereHas('profile', function ($query) use ($ptId) {
                $query->where('pt_id', $ptId);
            })
                ->with(['profile', 'position', 'division', 'payslips' => function ($q) use ($month, $year) {
                    $q->where('period_month', $month)
                        ->where('period_year', $year);
                }])
                ->get()
                ->map(function ($employee) {
                    // Determine status
                    $payslip = $employee->payslips->first();

                    $employee->payslip_status = $payslip ? $payslip->status : 'BELUM_DIBUAT'; // Custom status for UI
                    $employee->latest_payslip = $payslip;

                    return $employee;
                });
        }

        return view('hr.payroll.index', compact('employees', 'pts', 'month', 'year', 'ptId'));
    }

    public function create(Request $request)
    {
        Gate::authorize('manage-payroll');

        // Prioritize request input (query param), then old input (from validation redirect)
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
                'month' => $month,
                'year' => $year,
                'pt_id' => $request->input('filter_pt_id'),
            ])->with('warning', 'Slip gaji untuk periode ini sudah ada. Mengalihkan ke halaman edit.');
        }

        return view('hr.payroll.form', compact('user', 'month', 'year'));
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

        $data = $request->except(['_token', 'filter_month', 'filter_year', 'filter_pt_id']);

        // Hitung Totals
        // Pendapatan
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

        // Potongan
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
            \Illuminate\Support\Facades\Mail::to($payslip->user->email)->send(new \App\Mail\PayslipPublishedMail($payslip));
        }

        return redirect()->route('hr.payroll.index', [
            'month' => $request->input('filter_month', $request->period_month),
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

        return view('hr.payroll.form', compact('payslip', 'user', 'month', 'year'));
    }

    public function update(Request $request, Payslip $payslip)
    {
        Gate::authorize('manage-payroll');

        $request->validate([
            'status' => 'required|in:DRAFT,PUBLISHED',
        ]);

        $data = $request->except(['_token', '_method', 'filter_month', 'filter_year', 'filter_pt_id']);

        // Hitung Totals (Sama seperti store)
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

        $payslip->update($data);

        // Send email if status is PUBLISHED
        if ($payslip->status === 'PUBLISHED' && $payslip->user->email) {
            \Illuminate\Support\Facades\Mail::to($payslip->user->email)->send(new \App\Mail\PayslipPublishedMail($payslip));
        }

        return redirect()->route('hr.payroll.index', [
            'month' => $request->input('filter_month', $payslip->period_month),
            'year' => $request->input('filter_year', $payslip->period_year),
            'pt_id' => $request->input('filter_pt_id', $payslip->user->profile->pt_id ?? null),
        ])->with('success', 'Slip Gaji berhasil diperbarui.' . ($payslip->status === 'PUBLISHED' ? ' Email notifikasi telah dikirim.' : ''));
    }
}
