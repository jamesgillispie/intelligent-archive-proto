<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Models\Memory\ResearchTrail;
use App\Services\Trails\TrailService;
use Illuminate\Http\RedirectResponse;
use App\Services\Memory\ConsentService;
use App\Services\Memory\TrailMemoryService;
use App\Twill\Capsules\Artworks\Models\Artwork;

class TrailsController extends Controller
{
    public function __construct(
        private readonly TrailService $trails,
        private readonly ConsentService $consent,
        private readonly TrailMemoryService $trailMemory,
    ) {}

    public function index(Request $request): View
    {
        $trails = $request->user()->trails()->withCount('items')->orderByDesc('last_activity_at')->get();

        return view('site.account.trails', [
            'activeTrails' => $trails->where('status', ResearchTrail::STATUS_ACTIVE)->values(),
            'archivedTrails' => $trails->where('status', ResearchTrail::STATUS_ARCHIVED)->values(),
            // The quiet "continue where I left off" — the Iris pointer
            // supplies the hint, SQL verifies, a miss rebuilds (flow B).
            'continueTrail' => $this->trailMemory->activeTrail($request->user()),
            'paused' => $this->consent->isPaused($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $this->validate($request, [
            'title' => ['required', 'string', 'max:120'],
            'intent_note' => ['nullable', 'string', 'max:2000'],
            'artwork_id' => ['nullable', Rule::exists(Artwork::class, 'id')],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $artwork = isset($input['artwork_id']) ? Artwork::displayable()->findOrFail($input['artwork_id']) : null;

        $trail = $this->trails->start(
            $request->user(),
            $input['title'],
            $input['intent_note'] ?? null,
            $artwork,
            $input['note'] ?? null,
        );

        return redirect()
            ->route('account.trails.show', $trail->id)
            ->with('status', "Started the trail “{$trail->title}”.");
    }

    public function show(Request $request, int $trail): View
    {
        $trail = $request
            ->user()
            ->trails()
            ->with([
                'items.artwork.primaryImage',
                'items.artwork.medias',
                'items.artwork.artists',
                'items.artwork.slugs',
                'notes',
            ])
            ->findOrFail($trail);

        $this->trails->recordRevisit($trail);

        // Open suggestions render until answered — the Recommendation rows
        // are the durable record of what was shown, so nothing depends on
        // the redirect that created them. Works that stopped being
        // displayable since simply don't render.
        $open = $trail->recommendations()->open()->orderBy('id')->get();

        $suggested = Artwork::displayable()
            ->with(['primaryImage', 'medias', 'slugs'])
            ->findMany($open->pluck('artwork_id'))
            ->keyBy('id');

        $suggestions = $open
            ->filter(fn($recommendation) => $suggested->has($recommendation->artwork_id))
            ->each(
                fn($recommendation) => $recommendation->setRelation(
                    'artwork',
                    $suggested->get($recommendation->artwork_id),
                ),
            )
            ->groupBy('kind');

        $paused = $this->consent->isPaused($request->user());

        return view('site.account.trail', [
            'trail' => $trail,
            'paused' => $paused,
            'continuationSuggestions' => $suggestions->get('continuation', collect()),
            'counterpointSuggestions' => $suggestions->get('counterpoint', collect()),
            'canExpand' =>
                !$paused &&
                !$trail->isArchived() &&
                $trail->items->count() >= (int) config('trails.expansion_min_items'),
        ]);
    }

    public function archive(Request $request, int $trail): RedirectResponse
    {
        $trail = $request->user()->trails()->findOrFail($trail);

        $this->trails->archive($trail);

        return redirect()
            ->route('account.trails')
            ->with('status', "Archived “{$trail->title}”.");
    }

    public function reactivate(Request $request, int $trail): RedirectResponse
    {
        $trail = $request->user()->trails()->findOrFail($trail);

        $this->trails->reactivate($trail);

        return redirect()
            ->route('account.trails')
            ->with('status', "Reactivated “{$trail->title}”.");
    }

    public function destroy(Request $request, int $trail): RedirectResponse
    {
        $trail = $request->user()->trails()->findOrFail($trail);

        $this->trails->delete($trail);

        return redirect()
            ->route('account.memory')
            ->with('status', "Deleted the trail “{$trail->title}”, its items and its notes.");
    }
}
