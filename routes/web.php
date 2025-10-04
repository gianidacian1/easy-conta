<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ZDocumentController;
use App\Http\Controllers\DashboardController;



Route::get('/test', [TestController::class, 'test'])
    ->name('test');

Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

// dashboard
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('dashboard', [DashboardController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.store');

Route::put('dashboard/{dashboard}', [DashboardController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.update');

Route::delete('dashboard/{dashboard}', [DashboardController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.destroy');

Route::delete('dashboard/bulk-delete', [DashboardController::class, 'bulkDelete'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.bulkDelete');

//balante
Route::get('balances', [BalanceController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('balances');
Route::post('balances/upload', [BalanceController::class, 'upload'])
    ->middleware(['auth', 'verified'])
    ->name('balances.upload');

Route::delete('balances/bulk-delete', [BalanceController::class, 'bulkDelete'])
    ->middleware(['auth', 'verified'])
    ->name('balances.bulkDelete');


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
Route::put('z-documents/{zDocument}', [ZDocumentController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('z-documents.update');
Route::post('z-documents/adauga-plata', [ZDocumentController::class, 'addPayment'])
    ->middleware(['auth', 'verified'])
    ->name('z-documents.add-payment');


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
