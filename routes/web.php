<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\LesseeController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\OrdersheetController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// আবেদনের পাবলিক এন্ট্রি (লগইন ছাড়াই আবেদন জমা দেয়ার সুযোগ রাখলাম)
Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
Route::get('/ajax/property-dues/{property}', [ApplicationController::class, 'dues'])->name('ajax.property.dues');
Route::get('/ajax/properties', [PropertyController::class, 'select2'])->name('ajax.properties');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD (pages)
    Route::resource('properties', PropertyController::class)->middleware('role:developer,office_assistant');
    Route::resource('lessees', LesseeController::class)->middleware('role:developer,office_assistant');
    Route::resource('leases', LeaseController::class)->except(['show'])->middleware('role:developer,office_assistant');

    // DataTables JSON
    Route::get('/datatable/properties', [PropertyController::class, 'data'])->name('datatable.properties')->middleware('role:developer,office_assistant');
    Route::get('/datatable/lessees', [LesseeController::class, 'data'])->name('datatable.lessees')->middleware('role:developer,office_assistant');
    Route::get('/datatable/leases', [LeaseController::class, 'data'])->name('datatable.leases')->middleware('role:developer,office_assistant');

    // Select2 AJAX
    Route::get('/ajax/lessees', [LesseeController::class, 'select2'])->name('ajax.lessees')->middleware('role:developer,office_assistant');
    Route::get('/ajax/property/{property}', [PropertyController::class, 'showJson'])->name('ajax.property.show')->middleware('role:developer,office_assistant');

    // Reports (dues)
    Route::get('/reports/dues', [ReportController::class, 'dues'])->name('reports.dues');
    Route::get('/reports/dues-data', [ReportController::class, 'duesData'])->name('reports.dues.data');

    // Payments (renewal)
    Route::get('/payments/create/{lease}', [PaymentsController::class, 'create'])->name('payments.create')->middleware('role:developer,office_assistant');
    Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store')->middleware('role:developer,office_assistant');

    // Notice flow
    Route::post('/notices/preview', [NoticeController::class, 'preview'])->name('notices.preview')->middleware('role:developer,office_assistant');
    Route::post('/notices/preview-pdf', [NoticeController::class, 'previewPdf'])->name('notices.preview.pdf')->middleware('role:developer,office_assistant');
    Route::post('/notices/generate', [NoticeController::class, 'generate'])->name('notices.generate')->middleware('role:developer,office_assistant');

    Route::get('/reports/collections', [CollectionReportController::class, 'index'])->name('reports.collections');
    Route::get('/reports/collections-data', [CollectionReportController::class, 'data'])->name('reports.collections.data');

    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/data', [ApplicationController::class, 'data'])->name('applications.data');
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])
        ->name('applications.destroy')
        ->middleware('role:developer,office_assistant');

    // Order sheet
    Route::get('/ordersheet', [OrdersheetController::class, 'index'])->name('ordersheet.index');
});
