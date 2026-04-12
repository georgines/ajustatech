<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ShowServiceOrder;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ServiceOrderManagement;

Route::get('/ordens-servico', ShowServiceOrder::class)->name('service-order-show');
Route::get('/ordens-servico/cadastro', ServiceOrderManagement::class)->name('service-order-create');
Route::get('/ordens-servico/{serviceOrder}/editar', ServiceOrderManagement::class)
    ->whereUuid('serviceOrder')
    ->name('service-order-edit');
Route::get('/ordens-servico/{serviceOrder}/listar', ServiceOrderManagement::class)
    ->whereUuid('serviceOrder')
    ->name('service-order-list');
