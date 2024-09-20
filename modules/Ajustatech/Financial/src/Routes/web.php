<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\Financial\Livewire\ShowCompanyCash;
use Ajustatech\Financial\Livewire\CompanyCashManagement;

Route::get('/caixas-gerenciais', ShowCompanyCash::class)->name('companycash-show');
Route::get('/caixas-gerenciais/cadastro', CompanyCashManagement::class)->name('companycash-create');
Route::get('/caixa-gerenciais/{companycash}/editar', CompanyCashManagement::class)->name('companycash-edit');
