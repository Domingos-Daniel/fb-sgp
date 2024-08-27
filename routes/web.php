<?php

use App\Http\Controllers\BeneficiarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/', 'admin');
route::get('beneficiarios/{id}/export-pdf', [BeneficiarioController::class, 'exportPdf'])->name('beneficiarios.export-pdf');
