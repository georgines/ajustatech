<?php

use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Ajustatech\ServiceOrder\Livewire\Analysis\ShowAnalysisServices;
use Illuminate\Support\Facades\Route;

Route::get('/ordens-servico/analises', ShowAnalysisServices::class)->name('service-order-analyses-show');
Route::get('/ordens-servico/analises/cadastro', AnalysisManagement::class)->name('service-order-analyses-create');
Route::get('/ordens-servico/analises/{id}/editar', AnalysisManagement::class)->name('service-order-analyses-edit');
