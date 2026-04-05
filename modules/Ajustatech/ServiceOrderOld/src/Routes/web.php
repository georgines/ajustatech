<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\ServiceOrderOld\Http\Controllers\EquipmentTypeImageController;
use Ajustatech\ServiceOrderOld\Livewire\ShowServiceOrders;
use Ajustatech\ServiceOrderOld\Livewire\ShowServiceOrderDocuments;
use Ajustatech\ServiceOrderOld\Livewire\ShowServiceCatalog;
use Ajustatech\ServiceOrderOld\Livewire\ShowEquipmentTypes;
use Ajustatech\ServiceOrderOld\Livewire\NewServiceOrderManagement;
use Ajustatech\ServiceOrderOld\Livewire\EquipmentTypeManagement;
use Ajustatech\ServiceOrderOld\Livewire\ServiceCatalogManagement;
use Ajustatech\ServiceOrderOld\Livewire\ShowPendingAnalysisServices;
use Ajustatech\ServiceOrderOld\Livewire\AnalysisExecutionManagement;

Route::middleware(['web'])->group(function () {
    Route::get('/os/tipos-equipamento', ShowEquipmentTypes::class)
        ->name('service-order-equipment-types-show');

    Route::get('/os/tipos-equipamento/cadastro', EquipmentTypeManagement::class)
        ->name('service-order-equipment-types-create');

    Route::get('/os/tipos-equipamento/{id}/editar', EquipmentTypeManagement::class)
        ->name('service-order-equipment-types-edit');

    Route::get('/os/tipos-equipamento/{id}/imagem', EquipmentTypeImageController::class)
        ->name('service-order-equipment-types-image');

    Route::get('/os/nova', NewServiceOrderManagement::class)
        ->name('service-order-orders-create');

    Route::get('/os/ordens-servico', ShowServiceOrders::class)
        ->name('service-order-orders-show');

    Route::get('/os/ordens-servico/{id}/editar', NewServiceOrderManagement::class)
        ->name('service-order-orders-edit');

    Route::get('/os/ordens-servico/{id}/documentos', ShowServiceOrderDocuments::class)
        ->name('service-order-orders-documents');

    Route::get('/os/tipos-analise', ShowServiceCatalog::class)
        ->name('service-order-analysis-types-show');

    Route::get('/os/tipos-analise/cadastro', ServiceCatalogManagement::class)
        ->name('service-order-analysis-types-create');

    Route::get('/os/tipos-analise/{id}/editar', ServiceCatalogManagement::class)
        ->name('service-order-analysis-types-edit');

    Route::get('/os/analises/execucao', ShowPendingAnalysisServices::class)
        ->name('service-order-analysis-execution-queue');

    Route::get('/os/analises/execucao/{id}/comecar', AnalysisExecutionManagement::class)
        ->name('service-order-analysis-execution-start');

    // Backward-compatibility aliases (legacy "servicos")
    Route::get('/os/servicos', ShowServiceCatalog::class)
        ->name('service-order-services-show');

    Route::get('/os/servicos/cadastro', ServiceCatalogManagement::class)
        ->name('service-order-services-create');

    Route::get('/os/servicos/{id}/editar', ServiceCatalogManagement::class)
        ->name('service-order-services-edit');
});
