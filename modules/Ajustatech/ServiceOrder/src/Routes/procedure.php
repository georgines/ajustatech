<?php

use Ajustatech\ServiceOrder\Http\Controllers\ProcedureMediaController;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Ajustatech\ServiceOrder\Livewire\Procedure\ShowProcedures;
use Illuminate\Support\Facades\Route;

Route::get('/ordens-servico/procedimentos', ShowProcedures::class)->name('service-order-procedures-show');
Route::get('/ordens-servico/procedimentos/cadastro', ProcedureManagement::class)->name('service-order-procedures-create');
Route::get('/ordens-servico/procedimentos/{id}/editar', ProcedureManagement::class)->name('service-order-procedures-edit');
Route::get('/ordens-servico/procedimentos/midias/{id}/arquivo', ProcedureMediaController::class)->name('service-order-procedures-media-file');
