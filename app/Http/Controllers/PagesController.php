<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\View\Factory;
use App\Twill\Capsules\Pages\Models\Page;
use App\Http\Controllers\Traits\SetsMetadata;
use App\Twill\Capsules\Pages\Repositories\PageRepository;

class PagesController extends Controller
{
    use SetsMetadata;

    public function show(string $slug): Factory|View
    {
        $page = Page::query()->published()->forSlug($slug)->firstOrFail();

        $this->setMetadata($page);

        return view('site.page', app(PageRepository::class)->getShowData($page));
    }
}
