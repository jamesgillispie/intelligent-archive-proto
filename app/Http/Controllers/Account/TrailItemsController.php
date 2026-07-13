<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Memory\ResearchTrail;
use App\Services\Trails\TrailService;
use Illuminate\Http\RedirectResponse;
use App\Twill\Capsules\Artworks\Models\Artwork;

class TrailItemsController extends Controller
{
    public function __construct(private readonly TrailService $trails) {}

    /**
     * Flow A: the picker posts the chosen trail here. Flat route — the
     * trail is form input, so ownership is resolved, never trusted.
     */
    public function store(Request $request): RedirectResponse
    {
        $input = $this->validate($request, [
            'trail_id' => ['required', Rule::exists(ResearchTrail::class, 'id')],
            'artwork_id' => ['required', Rule::exists(Artwork::class, 'id')],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $trail = $request->user()->trails()->active()->findOrFail($input['trail_id']);
        $artwork = Artwork::displayable()->findOrFail($input['artwork_id']);

        $this->trails->addArtwork($trail, $artwork, $input['note'] ?? null);

        return redirect()
            ->route('account.trails.show', $trail->id)
            ->with('status', "Saved “{$artwork->title}” to “{$trail->title}”.");
    }

    public function destroy(Request $request, int $trail, int $item): RedirectResponse
    {
        $trail = $request->user()->trails()->findOrFail($trail);
        $item = $trail->items()->findOrFail($item);

        $this->trails->removeItem($item);

        return redirect()->route('account.trails.show', $trail->id)->with('status', 'Removed from this trail.');
    }
}
