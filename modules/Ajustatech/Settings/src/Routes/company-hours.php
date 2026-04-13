<?php

use Ajustatech\Settings\Livewire\CompanyHours\CompanyHoursSettingsManagement;
use Illuminate\Support\Facades\Route;

Route::get('/configuracoes/horarios-da-empresa', CompanyHoursSettingsManagement::class)
    ->name('settings-company-hours-show');

Route::get('/configuracoes/horarios-da-empresa/editar', CompanyHoursSettingsManagement::class)
    ->name('settings-company-hours-edit');
