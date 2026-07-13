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
use App\Http\Controllers\Account\SaveController;
use App\Http\Controllers\Account\NotesController;
use App\Http\Controllers\Account\SavesController;
use App\Http\Controllers\Account\ExportController;
use App\Http\Controllers\Account\MemoryController;
use App\Http\Controllers\Account\TrailsController;
use App\Http\Controllers\Account\ConsentController;
use App\Http\Controllers\Account\TrailItemsController;
use App\Http\Controllers\Account\MemoryPauseController;
use App\Http\Controllers\Account\MemoryEventsController;
use App\Http\Controllers\Account\MemoryDeletionController;
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
            Route::get('/consent', [ConsentController::class, 'show'])->name('consent');
            Route::post('/consent', [ConsentController::class, 'store'])->name('consent.renew');
            Route::get('/consent/revoke', [ConsentController::class, 'confirmRevoke'])->name('consent.revoke');
            Route::delete('/consent', [ConsentController::class, 'destroy'])->name('consent.destroy');

            // Inspection, deletion and the memory switches stay reachable
            // with stale consent or paused memory — the user can always
            // inspect and delete (charter #8). Ownership is enforced by
            // resolving everything through the authenticated user.
            Route::get('/memory', MemoryController::class)->name('memory');
            Route::get('/memory/delete', [MemoryDeletionController::class, 'confirm'])->name('memory.delete');
            Route::delete('/memory', [MemoryDeletionController::class, 'destroy'])->name('memory.destroy');
            Route::get('/export', [ExportController::class, 'show'])->name('export');
            Route::get('/export/download', [ExportController::class, 'download'])->name('export.download');
            Route::post('/memory/pause', [MemoryPauseController::class, 'pause'])->name('memory.pause');
            Route::post('/memory/resume', [MemoryPauseController::class, 'resume'])->name('memory.resume');
            Route::delete('/memory/events/{event}', [MemoryEventsController::class, 'destroy'])->name(
                'memory.events.destroy',
            );
            Route::delete('/saves/{save}', [SavesController::class, 'destroy'])->name('saves.destroy');
            Route::delete('/notes/{note}', [NotesController::class, 'destroy'])->name('notes.destroy');
            Route::delete('/trails/{trail}', [TrailsController::class, 'destroy'])->name('trails.destroy');
            Route::delete('/trails/{trail}/items/{item}', [TrailItemsController::class, 'destroy'])->name(
                'trails.items.destroy',
            );

            // Memory writes and working surfaces require current consent;
            // pause blocks the unsafe methods here.
            Route::middleware('consented')->group(function () {
                Route::get('/trails', [TrailsController::class, 'index'])->name('trails');
                Route::post('/trails', [TrailsController::class, 'store'])->name('trails.store');
                Route::post('/trails/items', [TrailItemsController::class, 'store'])->name('trails.items.store');
                Route::get('/trails/{trail}', [TrailsController::class, 'show'])->name('trails.show');
                Route::post('/trails/{trail}/archive', [TrailsController::class, 'archive'])->name('trails.archive');
                Route::post('/trails/{trail}/reactivate', [TrailsController::class, 'reactivate'])->name(
                    'trails.reactivate',
                );
                Route::post('/trails/{trail}/notes', [NotesController::class, 'store'])->name('trails.notes.store');
                Route::get('/save/{slug}', SaveController::class)->name('save');
                Route::post('/saves', [SavesController::class, 'store'])->name('saves.store');
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
