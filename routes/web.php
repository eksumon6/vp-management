<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\LesseeController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CollectionReportController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OrdersheetController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Route::get('/', fn() => redirect()->route('reports.dues'));



// CRUD (pages)
Route::resource('properties', PropertyController::class);
Route::resource('lessees',   LesseeController::class);
Route::resource('leases',    LeaseController::class)->except(['show']);

// DataTables JSON
Route::get('/datatable/properties', [PropertyController::class, 'data'])->name('datatable.properties');
Route::get('/datatable/lessees',   [LesseeController::class,   'data'])->name('datatable.lessees');
Route::get('/datatable/leases',    [LeaseController::class,    'data'])->name('datatable.leases');

// Select2 AJAX
Route::get('/ajax/properties', [PropertyController::class, 'select2'])->name('ajax.properties');
Route::get('/ajax/lessees',    [LesseeController::class,   'select2'])->name('ajax.lessees');

// Property quick fetch
Route::get('/ajax/property/{property}', [PropertyController::class, 'showJson'])->name('ajax.property.show');

// Reports (dues)
Route::get('/reports/dues',      [ReportController::class, 'dues'])->name('reports.dues');
Route::get('/reports/dues-data', [ReportController::class, 'duesData'])->name('reports.dues.data');

// Payments (renewal)
Route::get('/payments/create/{lease}', [PaymentsController::class, 'create'])->name('payments.create');
Route::post('/payments',               [PaymentsController::class, 'store'])->name('payments.store');

// Notice flow
Route::post('/notices/preview',     [NoticeController::class, 'preview'])->name('notices.preview');        // HTML preview screen
Route::post('/notices/preview-pdf', [NoticeController::class, 'previewPdf'])->name('notices.preview.pdf'); // inline popup preview
Route::post('/notices/generate',    [NoticeController::class, 'generate'])->name('notices.generate');      // final generate & download


// Route::get('/reports/collections', [CollectionReportController::class, 'index'])->name('reports.collections');

Route::get('/reports/collections', [\App\Http\Controllers\CollectionReportController::class, 'index'])
    ->name('reports.collections');
Route::get('/reports/collections-data', [\App\Http\Controllers\CollectionReportController::class, 'data'])
    ->name('reports.collections.data');


Route::get('/applications',              [ApplicationController::class,'index'])->name('applications.index');
Route::get('/applications/data',         [ApplicationController::class,'data'])->name('applications.data');
Route::get('/applications/create',       [ApplicationController::class,'create'])->name('applications.create');
Route::post('/applications',             [ApplicationController::class,'store'])->name('applications.store');
Route::delete('/applications/{application}', [ApplicationController::class,'destroy'])->name('applications.destroy');

// AJAX: property dues
Route::get('/ajax/property-dues/{property}', [ApplicationController::class,'dues'])->name('ajax.property.dues');

// Order sheet
Route::get('/ordersheet', [OrdersheetController::class, 'index'])->name('ordersheet.index');