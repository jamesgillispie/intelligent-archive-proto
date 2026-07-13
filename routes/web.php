<?php

use App\Http\Controllers\Manifest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ArtistsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\ArtworksController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\Account\TrailsController;
use App\Http\Controllers\Account\ConsentController;
use App\Http\Controllers\Account\MemoryActivatedController;

App::booted(function () {
    // Sitemap
    if (!config('sitemap.static')) {
        Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap.xml');
        Route::get('/sitemap_{slug}.xml', [SitemapController::class, 'sitemapShow'])->name('sitemapshow.xml');
    }

    Route::get('/site.webmanifest', [Manifest::class, 'show'])->name('front.site-manifest');

    // Account — auth'd and consent-gated; the 'private' group carries the
    // session/CSRF stack, and staying outside the cache middleware group is
    // what keeps memory pages out of the response cache. Routes registered
    // in App::booted() get no middleware group implicitly.
    Route::middleware(['private', 'auth'])
        ->prefix('account')
        ->name('account.')
        ->group(function () {
            Route::get('/memory-activated', MemoryActivatedController::class)->name('memory-activated');
            Route::get('/consent', ConsentController::class)->name('consent');

            Route::middleware('consented')->group(function () {
                Route::get('/trails', TrailsController::class)->name('trails');
            });
        });

    Route::middleware(['cache:public;max_age=60;s_maxage=3600'])->group(function () {
        Route::get('/', HomepageController::class)->name('home');
        Route::get('/about', AboutController::class)->name('about.index');
        Route::get('/contact', ContactController::class)->name('contact.index');

        Route::get('/articles', [ArticlesController::class, 'index'])->name('articles.index');
        Route::get('/articles/{slug}', [ArticlesController::class, 'show'])->name('articles.show');

        // Archive (anonymous browse/search; registered before the pages catch-all)
        Route::get('/archive', ArchiveController::class)->name('archive.index');
        Route::get('/archive/artworks', [ArtworksController::class, 'index'])->name('archive.artworks.index');
        Route::get('/archive/artworks/{slug}', [ArtworksController::class, 'show'])->name('archive.artworks.show');
        Route::get('/archive/artists', [ArtistsController::class, 'index'])->name('archive.artists.index');
        Route::get('/archive/artists/{slug}', [ArtistsController::class, 'show'])->name('archive.artists.show');

        Route::get('/{slug}', [PagesController::class, 'show'])->name('pages.show');
    });
});
