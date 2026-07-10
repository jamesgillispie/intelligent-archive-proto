<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        if (empty(($content = Cache::store('database')->get('sitemap.xml')))) {
            abort(404);
        }

        return response($content)->header('Content-Type', 'text/xml');
    }

    public function sitemapShow(string $slug): Response
    {
        if (empty(($content = Cache::store('database')->get($slug . '.xml')))) {
            abort(404);
        }

        return response($content)->header('Content-Type', 'text/xml');
    }
}
