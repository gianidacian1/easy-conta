<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ZDocumentController;



Route::get('/test', [TestController::class, 'test'])
    ->name('test');

Route::get('/', function () {
    return redirect('/documents');
})->name('home');

//balante
// documents
Route::get('balances', [BalanceController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('balances');
Route::post('balances/upload', [BalanceController::class, 'upload'])
    ->middleware(['auth', 'verified'])
    ->name('balances.upload');


// documents
Route::get('documents', [DocumentController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('documents');

Route::get('documents/{document}', [DocumentController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('documents.show');

Route::post('documents/upload', [DocumentController::class, 'upload'])
    ->middleware(['auth', 'verified'])
    ->name('documents.upload');


Route::post('documents/process', [DocumentController::class, 'process'])
    ->middleware(['auth', 'verified'])
    ->name('documents.process');

//z

Route::get('/z-documents', [ZDocumentController::class, 'index']);
Route::post('z-documents/upload', [ZDocumentController::class, 'upload'])
    ->middleware(['auth', 'verified'])
    ->name('z-documents.upload');


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
