<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Trails\TrailExpansionService;

class TrailExpansionController extends Controller
{
    public function __construct(private readonly TrailExpansionService $expansion) {}

    /**
     * Flow C: "Expand this trail". Every suggestion is persisted the
     * moment it is shown, so the redirect loses nothing — the trail page
     * renders open suggestions until they are answered.
     */
    public function __invoke(Request $request, int $trail): RedirectResponse
    {
        $trail = $request->user()->trails()->findOrFail($trail);

        $created = $this->expansion->expand($trail);

        $status =
            $created === []
                ? 'Nothing new to suggest right now — everything the record connects is already here, or was dismissed.'
                : sprintf(
                    '%d %s below — each one explains itself.',
                    count($created),
                    count($created) === 1 ? 'suggestion' : 'suggestions',
                );

        return redirect()->route('account.trails.show', $trail->id)->with('status', $status);
    }
}
