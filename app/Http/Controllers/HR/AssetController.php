<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Pt;
use App\Models\User;
use App\Services\Image\ImageCompressor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssetController extends Controller
{
    public function __construct(protected ImageCompressor $imageCompressor) {}

    public function index(Request $request)
    {
        $assets = Asset::with(['category', 'currentUser', 'currentPt'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('q')->toString().'%';
                $query->where(function ($query) use ($keyword): void {
                    $query->where('asset_code', 'like', $keyword)
                        ->orWhere('name', 'like', $keyword)
                        ->orWhere('serial_number', 'like', $keyword)
                        ->orWhere('hostname', 'like', $keyword)
                        ->orWhere('email_laptop', 'like', $keyword);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('asset_status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('hr.assets.index', compact('assets'));
    }

    public function create()
    {
        return view('hr.assets.create', [
            'users' => $this->activeUsers(),
            'pts' => Pt::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAsset($request);

        $asset = DB::transaction(function () use ($request, $validated) {
            $asset = Asset::create($this->assetPayload($request, $validated) + [
                'asset_status' => $request->filled('current_user_id') ? Asset::STATUS_ASSIGNED : Asset::STATUS_AVAILABLE,
                'created_by' => $request->user()->id,
            ]);

            if ($asset->current_user_id) {
                $asset->movements()->create([
                    'movement_type' => 'ASSIGN',
                    'to_user_id' => $asset->current_user_id,
                    'to_pt_id' => $asset->current_pt_id,
                    'condition_after' => $asset->condition_status,
                    'movement_date' => $validated['movement_date'] ?? now()->toDateString(),
                    'handover_document_path' => $this->storeAssetImage($request, 'handover_document', 'asset-handover-documents', 'handover_'),
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $request->user()->id,
                ]);
            }

            return $asset;
        });

        return redirect()->route('hr.assets.show', $asset)->with('success', 'Asset berhasil ditambahkan.');
    }

    public function edit(Asset $asset)
    {
        return view('hr.assets.edit', [
            'asset' => $asset->load('category'),
            'pts' => Pt::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $this->validateAsset($request, $asset);

        $asset->update($this->assetPayload($request, $validated, $asset));

        return redirect()->route('hr.assets.show', $asset)->with('success', 'Asset berhasil diperbarui.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'currentUser', 'currentPt', 'movements.fromUser', 'movements.toUser']);

        return view('hr.assets.show', [
            'asset' => $asset,
            'users' => $this->activeUsers(),
            'pts' => Pt::orderBy('name')->get(),
        ]);
    }

    public function storeMovement(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'movement_type' => ['required', Rule::in(['ASSIGN', 'TRANSFER', 'RETURN', 'SERVICE', 'LOST', 'DISPOSAL'])],
            'to_user_id' => [Rule::requiredIf(fn () => in_array($request->input('movement_type'), ['ASSIGN', 'TRANSFER'], true)), 'nullable', 'exists:users,id'],
            'to_pt_id' => ['nullable', 'exists:pts,id'],
            'condition_after' => ['nullable', 'string', 'max:30'],
            'movement_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $asset, $validated): void {
            $asset->movements()->create([
                'movement_type' => $validated['movement_type'],
                'from_user_id' => $asset->current_user_id,
                'to_user_id' => $validated['to_user_id'] ?? null,
                'from_pt_id' => $asset->current_pt_id,
                'to_pt_id' => $validated['to_pt_id'] ?? null,
                'condition_before' => $asset->condition_status,
                'condition_after' => $validated['condition_after'] ?? $asset->condition_status,
                'movement_date' => $validated['movement_date'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $asset->update($this->movementAssetState($validated, $asset));
        });

        return redirect()->route('hr.assets.show', $asset)->with('success', 'Riwayat asset berhasil dicatat.');
    }

    private function validateAsset(Request $request, ?Asset $asset = null): array
    {
        return $request->validate([
            'asset_code' => ['required', 'string', 'max:50', Rule::unique('assets', 'asset_code')->ignore($asset)],
            'category_name' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,gif,bmp,tif,tiff,avif', 'max:2048'],
            'handover_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,gif,bmp,tif,tiff,avif', 'max:2048'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'hostname' => ['nullable', 'string', 'max:100'],
            'email_laptop' => ['nullable', 'email', 'max:150'],
            'condition_status' => ['required', 'string', 'max:30'],
            'current_user_id' => ['nullable', 'exists:users,id'],
            'current_pt_id' => ['nullable', 'exists:pts,id'],
            'purchase_date' => ['nullable', 'date'],
            'movement_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function assetPayload(Request $request, array $validated, ?Asset $asset = null): array
    {
        $categoryId = null;
        if ($request->filled('category_name')) {
            $categoryId = AssetCategory::firstOrCreate(['name' => $validated['category_name']])->id;
        }

        return [
            'asset_code' => $validated['asset_code'],
            'asset_category_id' => $categoryId,
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'model' => $validated['model'] ?? null,
            'photo_path' => $request->hasFile('photo') ? $this->storeAssetImage($request, 'photo', 'asset-photos', 'asset_') : $asset?->photo_path,
            'serial_number' => $validated['serial_number'] ?? null,
            'hostname' => $validated['hostname'] ?? null,
            'email_laptop' => $validated['email_laptop'] ?? null,
            'condition_status' => $validated['condition_status'],
            'current_user_id' => $validated['current_user_id'] ?? $asset?->current_user_id,
            'current_pt_id' => $validated['current_pt_id'] ?? $asset?->current_pt_id,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function movementAssetState(array $validated, Asset $asset): array
    {
        $status = match ($validated['movement_type']) {
            'ASSIGN', 'TRANSFER' => Asset::STATUS_ASSIGNED,
            'SERVICE' => Asset::STATUS_SERVICE,
            'LOST' => Asset::STATUS_LOST,
            'DISPOSAL' => Asset::STATUS_DISPOSAL,
            default => Asset::STATUS_AVAILABLE,
        };

        return [
            'asset_status' => $status,
            'current_user_id' => in_array($validated['movement_type'], ['ASSIGN', 'TRANSFER'], true) ? ($validated['to_user_id'] ?? null) : null,
            'current_pt_id' => $validated['to_pt_id'] ?? null,
            'condition_status' => $validated['condition_after'] ?? $asset->condition_status,
        ];
    }

    private function storeAssetImage(Request $request, string $key, string $folder, string $prefix): ?string
    {
        if (! $request->hasFile($key)) {
            return null;
        }

        $file = $request->file($key);

        try {
            return $this->imageCompressor->compressAndStore($file, 'photo', $folder, $prefix);
        } catch (ValidationException $exception) {
            $extension = strtolower($file->getClientOriginalExtension());

            if (! in_array($extension, ['heic', 'heif'], true)) {
                throw $exception;
            }

            // ponytail: HEIC conversion depends on server codecs; keep upload usable until Imagick HEIC is guaranteed.
            return $file->store($folder, 'public');
        }
    }

    private function activeUsers()
    {
        return User::active()->orderBy('name')->get();
    }
}
