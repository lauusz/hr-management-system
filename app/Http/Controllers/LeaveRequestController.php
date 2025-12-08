<?php

namespace App\Http\Controllers;

use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = LeaveRequest::with(['user', 'approver'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        $typeFilter = $request->query('type');
        if ($typeFilter && in_array($typeFilter, LeaveType::values(), true)) {
            $query->where('type', $typeFilter);
        } else {
            $typeFilter = null;
        }

        $submittedRange = trim((string) $request->query('submitted_range'));

        if ($submittedRange !== '') {
            try {
                $parts = preg_split('/\s+(to|sampai)\s+/i', $submittedRange);

                if (count($parts) === 1) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = (clone $from)->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                } elseif (count($parts) >= 2) {
                    $from = Carbon::parse(trim($parts[0]))->startOfDay();
                    $to = Carbon::parse(trim($parts[1]))->endOfDay();

                    if ($from->gt($to)) {
                        $temp = $from;
                        $from = $to;
                        $to = $temp;
                    }

                    $query->whereBetween('created_at', [$from, $to]);
                }
            } catch (\Exception $e) {
                $submittedRange = null;
            }
        }

        $items = $query->paginate(100)->appends([
            'type'            => $typeFilter,
            'submitted_range' => $submittedRange,
        ]);

        return view('leave_requests.index', [
            'items'          => $items,
            'typeFilter'     => $typeFilter,
            'typeOptions'    => LeaveType::cases(),
            'submittedRange' => $submittedRange,
        ]);
    }

    public function create()
    {
        $this->authorize('create', LeaveRequest::class);
        return view('leave_requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'       => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['required', 'string'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => ['nullable', 'date'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $today = now()->startOfDay();
        $daysDiff = $today->diffInDays($start, false);

        $notesParts = [];

        if ($validated['type'] === LeaveType::CUTI->value) {
            if ($daysDiff < 7 && $daysDiff >= 0) {
                $notesParts[] = "Pengajuan dilakukan {$daysDiff} hari sebelum tanggal mulai cuti (kurang dari H-7). Pengajuan tetap bisa diproses, namun akan ada potongan sesuai kebijakan perusahaan.";
            }

            $user = Auth::user();
            $profile = $user?->profile;
            if ($profile && $profile->tgl_bergabung) {
                $joinStart = Carbon::parse($profile->tgl_bergabung)->startOfDay();
                $tenureYears = $joinStart->diffInYears($today);
                if ($tenureYears < 1) {
                    $notesParts[] = 'Kurang dari 1 tahun kerja — pengajuan cuti akan dipotong gaji.';
                }
            }
        }

        $notes = null;
        if (!empty($notesParts)) {
            $notes = implode("\n", $notesParts);
        }

        $isIzinTelat = $validated['type'] === LeaveType::IZIN_TELAT->value;
        if ($isIzinTelat && !$request->filled(['latitude', 'longitude'])) {
            return back()->withErrors('Lokasi harus diisi untuk izin telat.')->withInput();
        }

        $photoBasename = null;
        if ($request->hasFile('photo')) {
            $photoBasename = $this->storeSupportingFile($request->file('photo'), $isIzinTelat);
        }

        $type = $validated['type'];
        $initialStatus = match ($type) {
            LeaveType::CUTI->value, LeaveType::CUTI_KHUSUS->value => LeaveRequest::PENDING_SUPERVISOR,
            default => LeaveRequest::PENDING_HR,
        };

        LeaveRequest::create([
            'user_id'    => Auth::id(),
            'type'       => $type,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'reason'     => $validated['reason'],
            'photo'      => $photoBasename,
            'status'     => $initialStatus,
            'notes'      => $notes,
            'latitude'   => $validated['latitude'] ?? null,
            'longitude'  => $validated['longitude'] ?? null,
            'accuracy_m' => $validated['accuracy_m'] ?? null,
            'location_captured_at' => $validated['location_captured_at'] ?? now(),
        ]);

        $isIzinTelat = $type === LeaveType::IZIN_TELAT->value;

        if ($isIzinTelat) {
            return redirect()
                ->route('leave-requests.create')
                ->with('show_izin_telat_popup', true);
        }

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function show(LeaveRequest $leave_request)
    {
        $this->authorize('view', $leave_request);
        return view('leave_requests.show', ['item' => $leave_request->load('user', 'approver')]);
    }

    public function destroy(LeaveRequest $leave_request)
    {
        $this->authorize('delete', $leave_request);

        if (!in_array($leave_request->status, [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR], true)) {
            return back()->with('ok', 'Hanya pengajuan yang masih pending yang bisa dihapus.');
        }

        if ($leave_request->photo) {
            Storage::disk('public')->delete('leave_photos/' . $leave_request->photo);
        }

        $leave_request->delete();

        return back()->with('ok', 'Pengajuan dihapus.');
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'type'       => ['required', Rule::in(LeaveType::values())],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['nullable', 'string', 'max:5000'],
            'photo'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf,doc,docx,xls,xlsx', 'max:8192'],
            'status'     => ['nullable', Rule::in([
                LeaveRequest::PENDING_SUPERVISOR,
                LeaveRequest::PENDING_HR,
                LeaveRequest::STATUS_APPROVED,
                LeaveRequest::STATUS_REJECTED,
            ])],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => ['nullable', 'date'],
        ]);

        $isIzinTelat = $validated['type'] === LeaveType::IZIN_TELAT->value;
        if ($isIzinTelat && !$request->filled(['latitude', 'longitude'])) {
            return back()->withErrors('Lokasi harus diisi untuk izin telat.')->withInput();
        }

        if ($request->hasFile('photo')) {
            if ($leaveRequest->photo) {
                Storage::disk('public')->delete('leave_photos/' . $leaveRequest->photo);
            }
            $validated['photo'] = $this->storeSupportingFile($request->file('photo'), $isIzinTelat);
        }

        $leaveRequest->update($validated);

        return back()->with('success', 'Pengajuan diperbarui');
    }

    public function approve(LeaveRequest $leave_request)
    {
        $this->authorize('approve', $leave_request);

        $leave_request->update([
            'status'      => LeaveRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('ok', 'Pengajuan disetujui.');
    }

    public function reject(LeaveRequest $leave_request)
    {
        $this->authorize('approve', $leave_request);

        $leave_request->update([
            'status'      => LeaveRequest::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('ok', 'Pengajuan ditolak.');
    }

    protected function storeSupportingFile(UploadedFile $file, bool $compress = false): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $dir = 'leave_photos';
        $disk = Storage::disk('public');
        $gdLoaded = extension_loaded('gd');

        Log::info('Leave storeSupportingFile called', [
            'compress_flag' => $compress,
            'ext' => $ext,
            'gd_loaded' => $gdLoaded,
        ]);

        if (
            $compress
            && $gdLoaded
            && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)
        ) {
            Log::info('Leave compression branch entered', [
                'ext' => $ext,
            ]);

            try {
                $sourcePath = $file->getPathname();
                $info = getimagesize($sourcePath);
                if ($info === false) {
                    throw new \RuntimeException('Invalid image.');
                }

                $width = $info[0];
                $height = $info[1];

                $maxSide = 720;
                $scale = min($maxSide / max($width, 1), $maxSide / max($height, 1), 1);
                $newWidth = (int) round($width * $scale);
                $newHeight = (int) round($height * $scale);

                switch ($ext) {
                    case 'jpg':
                    case 'jpeg':
                        $srcImage = imagecreatefromjpeg($sourcePath);
                        break;
                    case 'png':
                        $srcImage = imagecreatefrompng($sourcePath);
                        break;
                    case 'webp':
                        if (!function_exists('imagecreatefromwebp')) {
                            throw new \RuntimeException('WEBP not supported.');
                        }
                        $srcImage = imagecreatefromwebp($sourcePath);
                        break;
                    default:
                        $srcImage = null;
                }

                if (!$srcImage) {
                    throw new \RuntimeException('Failed to create image resource.');
                }

                $dstImage = imagecreatetruecolor($newWidth, $newHeight);

                if ($ext === 'png' || $ext === 'webp') {
                    imagealphablending($dstImage, false);
                    imagesavealpha($dstImage, true);
                    $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
                    imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled(
                    $dstImage,
                    $srcImage,
                    0,
                    0,
                    0,
                    0,
                    $newWidth,
                    $newHeight,
                    $width,
                    $height
                );

                $filename = 'leave_' . uniqid('', true) . '.jpg';

                ob_start();
                imagejpeg($dstImage, null, 70);
                $contents = ob_get_clean();

                imagedestroy($srcImage);
                imagedestroy($dstImage);

                if ($contents === false) {
                    throw new \RuntimeException('Failed to encode JPEG.');
                }

                $disk->put($dir . '/' . $filename, $contents);

                Log::info('Leave compression success', [
                    'filename' => $filename,
                    'size_bytes' => strlen($contents),
                ]);

                return $filename;
            } catch (\Throwable $e) {
                Log::warning('Leave photo GD compression failed, fallback to original store', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Leave store fallback original', [
            'ext' => $ext,
        ]);

        $stored = $file->store($dir, 'public');

        Log::info('Leave store fallback stored', [
            'stored' => $stored,
        ]);

        return basename($stored);
    }
}
