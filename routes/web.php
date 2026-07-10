<?php

use App\Http\Controllers\Manifest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\HomepageController;

App::booted(function () {
    // Sitemap
    if (!config('sitemap.static')) {
        Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap.xml');
        Route::get('/sitemap_{slug}.xml', [SitemapController::class, 'sitemapShow'])->name('sitemapshow.xml');
    }

    Route::get('/site.webmanifest', [Manifest::class, 'show'])->name('front.site-manifest');

    Route::middleware(['cache:public;max_age=60;s_maxage=3600'])->group(function () {
        Route::get('/', HomepageController::class)->name('home');
        Route::get('/about', AboutController::class)->name('about.index');
        Route::get('/contact', ContactController::class)->name('contact.index');

        Route::get('/articles', [ArticlesController::class, 'index'])->name('articles.index');
        Route::get('/articles/{slug}', [ArticlesController::class, 'show'])->name('articles.show');

        Route::get('/{slug}', [PagesController::class, 'show'])->name('pages.show');
    });
});
