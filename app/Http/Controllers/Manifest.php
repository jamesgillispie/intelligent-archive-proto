<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class Manifest extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'name' => config('meta_tags.title.default'),
            'short_name' => config('app.name'),
            'start_url' => '/',
            'icons' => [
                [
                    'src' => asset('android-chrome-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => asset('android-chrome-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
                [
                    'src' => asset('android-chrome-192x192.png'),
                    'sizes' => '192x192',
                    'purpose' => 'maskable',
                ],
            ],
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ]);
    }
}
