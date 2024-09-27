<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\Financial\Livewire\ShowCompanyCash;
use Ajustatech\Financial\Livewire\CompanyCashManagement;
use Ajustatech\Financial\Livewire\ShowCompanyCashTransactions;
use Ajustatech\Financial\Livewire\CompanyCashTransactionsManagement;

Route::get('/caixas-gerenciais', ShowCompanyCash::class)->name('companycash-show');
Route::get('/caixas-gerenciais/cadastro', CompanyCashManagement::class)->name('companycash-create');
Route::get('/caixa-gerenciais/{companycash}/editar', CompanyCashManagement::class)->name('companycash-edit');


Route::get('/t/{id}', ShowCompanyCashTransactions::class)->name('company-cash-transactions-show');
// Route::get('/uri/cadastro', CompanyCashTransactionsManagement::class)->name('company-cash-transactions-create');
// Route::get('/uri/{id}/editar', CompanyCashTransactionsManagement::class)->name('company-cash-transactions-edit');
