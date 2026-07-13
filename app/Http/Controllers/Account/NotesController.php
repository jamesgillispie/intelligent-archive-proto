<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Memory\NoteService;
use Illuminate\Http\RedirectResponse;

class NotesController extends Controller
{
    public function __construct(private readonly NoteService $notes) {}

    public function store(Request $request, int $trail): RedirectResponse
    {
        $input = $this->validate($request, [
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $trail = $request->user()->trails()->findOrFail($trail);

        $this->notes->write($request->user(), $trail, $input['body']);

        return redirect()->route('account.trails.show', $trail->id)->with('status', 'Note added.');
    }

    public function destroy(Request $request, int $note): RedirectResponse
    {
        $note = $request->user()->notes()->findOrFail($note);

        $this->notes->delete($note);

        return back()->with('status', 'Note deleted.');
    }
}
