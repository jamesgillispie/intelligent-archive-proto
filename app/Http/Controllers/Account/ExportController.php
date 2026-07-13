<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Services\Memory\MemoryExportService;

/**
 * Full memory export — a first-class right, not a settings burial, so it
 * lives in the auth-only tier and works under paused or stale consent.
 */
class ExportController extends Controller
{
    public function __construct(private readonly MemoryExportService $exports) {}

    public function show(Request $request): View
    {
        $export = $this->exports->export($request->user());

        return view('site.account.export', [
            'export' => $export,
            // The page shows the document verbatim — the export IS what
            // you can read here, nothing more leaves in the file.
            'pretty' => json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function download(Request $request): JsonResponse
    {
        $filename = 'memory-export-' . now()->format('Y-m-d') . '.json';

        return response()->json(
            $this->exports->export($request->user()),
            200,
            ['Content-Disposition' => "attachment; filename=\"{$filename}\""],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
