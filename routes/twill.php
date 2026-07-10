<?php

use A17\Twill\Facades\TwillRoutes;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Twill\ImportBatchesController;

// Register Twill routes here eg.
// TwillRoutes::module('posts');

// Read-only import dashboard (routes here run inside the Twill admin
// group: auth middleware, admin prefix, and the twill. name prefix).
Route::get('import-batches', ImportBatchesController::class)->name('importBatches.index');
