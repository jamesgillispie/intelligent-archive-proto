<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Services\Memory\ConsentService;

class MemoryController extends Controller
{
    public function __construct(private readonly ConsentService $consent) {}

    /**
     * The SQL-only memory dashboard: everything the archive holds about
     * this user, inspectable and deletable — regardless of pause state or
     * consent staleness. Iris context inspection joins in Phase 5.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('site.account.memory', [
            'consent' => $user->memoryConsent,
            'consentCurrent' => $this->consent->hasCurrentConsent($user),
            'paused' => $this->consent->isPaused($user),
            'saves' => $user->artworkSaves()->with('artwork')->latest()->get(),
            'trailCount' => $user->trails()->count(),
            'noteCount' => $user->notes()->count(),
            'events' => $user->memoryEvents()->orderByDesc('occurred_at')->orderByDesc('id')->paginate(25),
        ]);
    }
}
