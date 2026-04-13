<?php

use Ajustatech\Settings\Livewire\ServiceOrder\ServiceOrderSettingsManagement;
use Illuminate\Support\Facades\Route;

Route::get('/ordens-servico/configuracoes', ServiceOrderSettingsManagement::class)
    ->name('settings-service-order-show');

Route::get('/ordens-servico/configuracoes/editar', ServiceOrderSettingsManagement::class)
    ->name('settings-service-order-edit');
