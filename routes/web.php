<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TicketPrintController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::livewire('/login', 'pages::login.⚡index')->name('login');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/', 'pages::dashboard.⚡index')->name('dashboard');


Route::get('/tickets/{id}/print', [TicketPrintController::class, 'print'])->name('tickets.print')->middleware(['auth']);

    Route::livewire('/pegawai', 'pages::pegawai.⚡index')->name('pegawai');
    Route::livewire('/departement', 'pages::departement.⚡index')->name('departement');

    //route untuk aset
    Route::livewire('/aset/create', 'pages::aset.⚡created')->name('aset.create');
    Route::livewire('/aset', 'pages::aset.⚡index')->name('aset.index');
    Route::livewire('/aset/edit/{aset}', 'pages::aset.⚡edit')->name('aset.edit');
    Route::livewire('/aset/show/{id}', 'pages::aset.⚡show')->name('aset.show');

    //route untuk kategori aset
    Route::livewire('/categories', 'pages::category.⚡index')->name('category.index');

    //route untuk profile
    Route::livewire('/profile', 'pages::profile.⚡index')->name('profile.index');

    //route report
    Route::livewire('/report', 'pages::report.⚡index')->name('report.index');



    Route::livewire('/tickets/{id}/details', 'pages::tickets.⚡details')->name('tickets.details');
    // Route::livewire('/tickets/manage', 'pages::tickets.⚡manage')->name('tickets.manage');
    Route::livewire('/tickets/create', 'pages::tickets.⚡created')->name('tickets.create');
    Route::livewire('/tickets', 'pages::tickets.⚡index')->name('tickets.index');
    Route::livewire('/tickets/edit/{id}', 'pages::tickets.⚡edit')->name('tickets.edit');



});
