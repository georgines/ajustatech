<?php

use Ajustatech\Settings\Http\Controllers\Company\CompanyLogoController;
use Ajustatech\Settings\Livewire\Company\CompanySettingsManagement;
use Illuminate\Support\Facades\Route;

Route::get('/configuracoes/empresa', CompanySettingsManagement::class)
    ->name('settings-company-edit');

Route::get('/configuracoes/empresa/editar', CompanySettingsManagement::class)
    ->name('settings-company-edit-legacy');

Route::get('/configuracoes/empresa/logo', CompanyLogoController::class)
    ->name('settings-company-logo');
