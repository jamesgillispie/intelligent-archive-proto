<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use App\Http\Controllers\Traits\SetsMetadata;
use App\Twill\Capsules\AboutLandings\Models\AboutLanding;
use App\Twill\Capsules\AboutLandings\Repositories\AboutLandingRepository;

class AboutController extends Controller
{
    use SetsMetadata;

    public function __invoke(Request $request): Factory|View
    {
        $landing = AboutLanding::query()->firstOrFail();

        $this->setMetadata($landing);

        return view('site.about', app(AboutLandingRepository::class)->getShowData($landing));
    }
}
