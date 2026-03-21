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
use Ajustatech\Financial\Livewire\ShowPaymentMethods;
use Ajustatech\Financial\Livewire\PaymentMethodManagement;
use Ajustatech\Financial\Livewire\ShowCardBrands;
use Ajustatech\Financial\Livewire\CardBrandManagement;

Route::get('/caixas-gerenciais', ShowCompanyCash::class)->name('companycash-show');
Route::get('/caixas-gerenciais/cadastro', CompanyCashManagement::class)->name('companycash-create');
Route::get('/caixa-gerenciais/{companycash}/editar', CompanyCashManagement::class)->name('companycash-edit');
Route::get('/t/{id}', ShowCompanyCashTransactions::class)->name('company-cash-transactions-show');

Route::get('/contas-a-pagar', ShowPayables::class)->name('financial-payables-show');
Route::get('/contas-a-pagar/cadastro', PayableManagement::class)->name('financial-payables-create');

Route::get('/contas-a-receber', ShowReceivables::class)->name('financial-receivables-show');
Route::get('/contas-a-receber/cadastro', ReceivableManagement::class)->name('financial-receivables-create');

Route::get('/caixa-vendas/diario', SalesCashDailyManagement::class)->name('financial-sales-cash-daily');

Route::get('/formas-pagamento', ShowPaymentMethods::class)->name('financial-payment-methods-show');
Route::get('/formas-pagamento/cadastro', PaymentMethodManagement::class)->name('financial-payment-methods-create');
Route::get('/formas-pagamento/{id}/editar', PaymentMethodManagement::class)->name('financial-payment-methods-edit');

Route::get('/bandeiras-cartao', ShowCardBrands::class)->name('financial-card-brands-show');
Route::get('/bandeiras-cartao/cadastro', CardBrandManagement::class)->name('financial-card-brands-create');
Route::get('/bandeiras-cartao/{id}/editar', CardBrandManagement::class)->name('financial-card-brands-edit');