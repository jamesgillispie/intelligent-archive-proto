<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Memory\ArtworkSaveService;
use App\Twill\Capsules\Artworks\Models\Artwork;

class SavesController extends Controller
{
    public function __construct(private readonly ArtworkSaveService $saves) {}

    public function store(Request $request): RedirectResponse
    {
        $input = $this->validate($request, [
            'artwork_id' => ['required', Rule::exists(Artwork::class, 'id')],
        ]);

        $artwork = Artwork::displayable()->findOrFail($input['artwork_id']);

        $this->saves->save($request->user(), $artwork);

        return redirect()
            ->route('account.memory')
            ->with('status', "Saved “{$artwork->title}” for later — it lives here, in your memory.");
    }

    public function destroy(Request $request, int $save): RedirectResponse
    {
        $save = $request->user()->artworkSaves()->findOrFail($save);

        $this->saves->remove($save);

        return redirect()->route('account.memory')->with('status', 'Save removed.');
    }
}
