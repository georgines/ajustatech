<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\Financial\Livewire\ShowCompanyCash;
use Ajustatech\Financial\Livewire\CompanyCashManagement;
use Ajustatech\Financial\Livewire\ShowCompanyCashTransactions;
use Ajustatech\Financial\Livewire\PayableManagement;
use Ajustatech\Financial\Livewire\ReceivableManagement;
use Ajustatech\Financial\Livewire\SalesCashDailyManagement;
use Ajustatech\Financial\Livewire\ShowPayables;
use Ajustatech\Financial\Livewire\ShowReceivables;

Route::get('/caixas-gerenciais', ShowCompanyCash::class)->name('companycash-show');
Route::get('/caixas-gerenciais/cadastro', CompanyCashManagement::class)->name('companycash-create');
Route::get('/caixa-gerenciais/{companycash}/editar', CompanyCashManagement::class)->name('companycash-edit');
Route::get('/t/{id}', ShowCompanyCashTransactions::class)->name('company-cash-transactions-show');

Route::get('/contas-a-pagar', ShowPayables::class)->name('financial-payables-show');
Route::get('/contas-a-pagar/cadastro', PayableManagement::class)->name('financial-payables-create');

Route::get('/contas-a-receber', ShowReceivables::class)->name('financial-receivables-show');
Route::get('/contas-a-receber/cadastro', ReceivableManagement::class)->name('financial-receivables-create');

Route::get('/caixa-vendas/diario', SalesCashDailyManagement::class)->name('financial-sales-cash-daily');