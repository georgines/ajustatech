<?php

use Ajustatech\Settings\Livewire\ServiceOrder\ServiceOrderSettingsManagement;
use Illuminate\Support\Facades\Route;

Route::get('/ordens-servico/configuracoes', ServiceOrderSettingsManagement::class)
    ->name('settings-service-order-show');

Route::redirect('/ordens-servico/configuracoes/editar', '/ordens-servico/configuracoes')
    ->name('settings-service-order-edit');


