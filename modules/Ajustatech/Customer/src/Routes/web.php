<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ShowCustomer;
use App\Livewire\CustomerManagement;
// use Ajustatech\Customer\Livewire\ShowCustomer;
// use Ajustatech\Customer\Livewire\CustomerManagement;

Route::get('/clientes', ShowCustomer::class)->name('customers-show');
Route::get('/clientes/cadastro', CustomerManagement::class)->name('customers-create');
Route::get('/clientes/{customer}/editar', CustomerManagement::class)->name('customers-edit');
