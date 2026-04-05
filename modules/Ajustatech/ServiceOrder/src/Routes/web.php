<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\ServiceOrder\Http\Controllers\ProcedureMediaController;
use Ajustatech\ServiceOrder\Livewire\ShowServiceOrder;
use Ajustatech\ServiceOrder\Livewire\ServiceOrderManagement;
use Ajustatech\ServiceOrder\Livewire\Procedure\ShowProcedures;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;

Route::get('/ordens-servico', ShowServiceOrder::class)->name('service-order-show');
Route::get('/ordens-servico/cadastro', ServiceOrderManagement::class)->name('service-order-create');
Route::get('/ordens-servico/{serviceOrder}/editar', ServiceOrderManagement::class)->name('service-order-edit');

Route::get('/ordens-servico/procedimentos', ShowProcedures::class)->name('service-order-procedures-show');
Route::get('/ordens-servico/procedimentos/cadastro', ProcedureManagement::class)->name('service-order-procedures-create');
Route::get('/ordens-servico/procedimentos/{id}/editar', ProcedureManagement::class)->name('service-order-procedures-edit');
Route::get('/ordens-servico/procedimentos/midias/{id}/arquivo', ProcedureMediaController::class)->name('service-order-procedures-media-file');
