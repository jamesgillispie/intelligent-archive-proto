<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Trails\TrailExpansionService;

class SuggestionsController extends Controller
{
    public function __construct(private readonly TrailExpansionService $expansion) {}

    public function accept(Request $request, int $recommendation): RedirectResponse
    {
        $recommendation = $request
            ->user()
            ->recommendations()
            ->with(['trail', 'artwork'])
            ->findOrFail($recommendation);

        $this->expansion->accept($recommendation);

        return redirect()
            ->route('account.trails.show', $recommendation->research_trail_id)
            ->with('status', "Saved “{$recommendation->artwork->title}” to “{$recommendation->trail->title}”.");
    }

    public function dismiss(Request $request, int $recommendation): RedirectResponse
    {
        $recommendation = $request
            ->user()
            ->recommendations()
            ->with(['trail', 'artwork'])
            ->findOrFail($recommendation);

        $this->expansion->dismiss($recommendation);

        return redirect()
            ->route('account.trails.show', $recommendation->research_trail_id)
            ->with('status', "Dismissed — “{$recommendation->artwork->title}” won’t be suggested again.");
    }
}
