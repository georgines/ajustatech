<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\ServiceOrder\Livewire\ShowServiceOrder;
use Ajustatech\ServiceOrder\Livewire\ServiceOrderManagement;

Route::get('/ordens-servico', ShowServiceOrder::class)->name('service-order-show');
Route::get('/ordens-servico/cadastro', ServiceOrderManagement::class)->name('service-order-create');
Route::get('/ordens-servico/{serviceOrder}/editar', ServiceOrderManagement::class)->name('service-order-edit');
