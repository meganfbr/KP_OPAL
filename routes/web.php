<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\PtppPdfController;
use App\Models\lapor_ptpp;

Route::get('/login', function () {
    return Redirect::route('filament.admin.pages.dashboard');
})->name('login');

Route::get('/', function () {
    return Redirect::route('filament.admin.pages.dashboard');
});

Route::get('/ptpp/view/{id}', function ($id) {
    $data = lapor_ptpp::findOrFail($id);
    return view('admin.perbaikan_pencegahan', compact('data'));
})->name('admin.perbaikan_pencegahan');

Route::get('/layanan', function () {
    return view('layanan');
})->name('layanan');
Route::get('/rekrutmen', function () {
    return view('rekrutmen');
})->name('rekrutmen');
Route::get('/visi-misi', function () {
    return view('visimisi');
});
Route::get('/inventaris', function () {
    return view('inventaris');
})->name('inventaris');

