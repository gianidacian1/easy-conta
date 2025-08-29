<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/documents');
})->name('home');

// documents
Route::get('documents', [DocumentController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('documents.index');

Route::get('documents/{document}', [DocumentController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('documents.show');

Route::post('documents/upload', [DocumentController::class, 'upload'])
    ->middleware(['auth', 'verified'])
    ->name('documents.upload');


Route::post('documents/process', [DocumentController::class, 'process'])
    ->middleware(['auth', 'verified'])
    ->name('documents.process');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
