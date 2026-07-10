<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use App\Http\Controllers\Traits\SetsMetadata;
use App\Twill\Capsules\Homepages\Models\Homepage;
use App\Twill\Capsules\Homepages\Repositories\HomepageRepository;

class HomepageController extends Controller
{
    use SetsMetadata;

    public function __invoke(Request $request): Factory|View
    {
        $landing = Homepage::query()->firstOrFail();

        $this->setMetadata($landing);

        return view('site.homepage', app(HomepageRepository::class)->getShowData($landing));
    }
}
