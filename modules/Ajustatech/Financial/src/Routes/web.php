<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\Financial\Livewire\ShowFinancial;
use Ajustatech\Financial\Livewire\FinancialManagement;

Route::get('/financial', ShowFinancial::class)->name('financial-show');
Route::get('/financial/cadastro', FinancialManagement::class)->name('financial-create');
Route::get('/financial/{financial}/editar', FinancialManagement::class)->name('financial-edit');
