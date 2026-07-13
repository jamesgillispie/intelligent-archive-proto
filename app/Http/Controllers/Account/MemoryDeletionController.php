<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Memory\MemoryDeletionService;

/**
 * Whole-memory deletion (flow F). Deletion is a right that survives pause
 * and stale consent, so both routes live in the auth-only tier; the
 * confirmation page states exactly what disappears before anything does.
 */
class MemoryDeletionController extends Controller
{
    public function __construct(private readonly MemoryDeletionService $deletion) {}

    public function confirm(Request $request): View
    {
        return view('site.account.memory-delete', [
            'counts' => $this->deletion->counts($request->user()),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->deletion->deleteAll($request->user());

        return redirect()
            ->route('account.memory')
            ->with(
                'status',
                'Everything the archive held about you is deleted. ' .
                    'The one entry below is the receipt; it is yours to delete too.',
            );
    }
}
