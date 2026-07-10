<?php

namespace App\Http\Controllers\Twill;

use Illuminate\View\View;
use App\Models\Source\SourceImport;
use App\Http\Controllers\Controller;
use App\Twill\Capsules\Artworks\Models\ImageAsset;

/**
 * Read-only import visibility for editors: "did last night's import
 * work?" without touching a terminal. Deliberately not a Twill module —
 * Twill manages the canonical layer only, never the raw source layer
 * (CLAUDE.md architecture boundary), so this page renders source-layer
 * bookkeeping without making it editable.
 */
class ImportBatchesController extends Controller
{
    public function __invoke(): View
    {
        $batches = SourceImport::query()->latest('id')->limit(25)->get();

        $primaryImages = ImageAsset::query()->where('is_primary', true);

        $imageSummary = [
            'downloaded' => (clone $primaryImages)->whereNotNull('media_id')->count(),
            'pending' => (clone $primaryImages)
                ->whereNull('media_id')
                ->where('validation_status', '!=', ImageAsset::VALIDATION_FAILED)
                ->count(),
            'failed' => (clone $primaryImages)->where('validation_status', ImageAsset::VALIDATION_FAILED)->count(),
        ];

        $flaggedImages = ImageAsset::query()
            ->with('artwork:id,title,nga_object_id')
            ->where('validation_status', ImageAsset::VALIDATION_FAILED)
            ->latest('validated_at')
            ->limit(50)
            ->get();

        return view('twill.imports.index', [
            'batches' => $batches,
            'imageSummary' => $imageSummary,
            'flaggedImages' => $flaggedImages,
        ]);
    }
}
