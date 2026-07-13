<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Services\Memory\ConsentService;
use App\Services\Memory\VisitorContextService;
use App\Services\RedisIris\RedisIrisMemoryContract;

class MemoryController extends Controller
{
    public function __construct(
        private readonly ConsentService $consent,
        private readonly RedisIrisMemoryContract $iris,
        private readonly VisitorContextService $visitors,
    ) {}

    /**
     * The memory dashboard: everything the archive holds about this user —
     * the durable SQL record and the derived Iris context, verbatim —
     * inspectable and deletable regardless of pause state or consent
     * staleness.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('site.account.memory', [
            'consent' => $user->memoryConsent,
            'consentCurrent' => $this->consent->hasCurrentConsent($user),
            'paused' => $this->consent->isPaused($user),
            'heldContext' => $this->iris->inspect($this->visitors->userPrefix($user)),
            'saves' => $user->artworkSaves()->with('artwork')->latest()->get(),
            'trailCount' => $user->trails()->count(),
            'noteCount' => $user->notes()->count(),
            'events' => $user->memoryEvents()->orderByDesc('occurred_at')->orderByDesc('id')->paginate(25),
        ]);
    }
}
