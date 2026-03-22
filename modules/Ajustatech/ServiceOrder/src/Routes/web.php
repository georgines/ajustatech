<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\ServiceOrder\Livewire\ShowServiceOrders;
use Ajustatech\ServiceOrder\Livewire\ShowServiceCatalog;
use Ajustatech\ServiceOrder\Livewire\ShowEquipmentTypes;
use Ajustatech\ServiceOrder\Livewire\NewServiceOrderManagement;
use Ajustatech\ServiceOrder\Livewire\EquipmentTypeManagement;
use Ajustatech\ServiceOrder\Livewire\ServiceCatalogManagement;

Route::middleware(['web'])->group(function () {
    Route::get('/os/tipos-equipamento', ShowEquipmentTypes::class)
        ->name('service-order-equipment-types-show');

    Route::get('/os/tipos-equipamento/cadastro', EquipmentTypeManagement::class)
        ->name('service-order-equipment-types-create');

    Route::get('/os/tipos-equipamento/{id}/editar', EquipmentTypeManagement::class)
        ->name('service-order-equipment-types-edit');

    Route::get('/os/nova', NewServiceOrderManagement::class)
        ->name('service-order-orders-create');

    Route::get('/os/ordens-servico', ShowServiceOrders::class)
        ->name('service-order-orders-show');

    Route::get('/os/servicos', ShowServiceCatalog::class)
        ->name('service-order-services-show');

    Route::get('/os/servicos/cadastro', ServiceCatalogManagement::class)
        ->name('service-order-services-create');

    Route::get('/os/servicos/{id}/editar', ServiceCatalogManagement::class)
        ->name('service-order-services-edit');
});
