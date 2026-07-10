<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use App\Http\Controllers\Traits\SetsMetadata;
use App\Twill\Capsules\ContactLandings\Models\ContactLanding;
use App\Twill\Capsules\ContactLandings\Repositories\ContactLandingRepository;

class ContactController extends Controller
{
    use SetsMetadata;

    public function __invoke(Request $request): Factory|View
    {
        $landing = ContactLanding::query()->firstOrFail();

        $this->setMetadata($landing);

        return view('site.contact', app(ContactLandingRepository::class)->getShowData($landing));
    }
}
