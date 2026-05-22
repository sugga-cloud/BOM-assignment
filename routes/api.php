<?php

use App\Http\Controllers\Api\V1\BomUploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/bom')->group(function () {
    Route::post('/upload', [BomUploadController::class, 'upload'])->name('api.bom.upload');
    Route::get('/status/{id}', [BomUploadController::class, 'status'])->name('api.bom.status');
    Route::get('/metrics', [BomUploadController::class, 'metrics'])->name('api.bom.metrics');
});
