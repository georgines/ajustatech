<?php

use Ajustatech\ServiceOrder\Http\Controllers\EquipmentTypeDocumentFileController;
use Ajustatech\ServiceOrder\Livewire\EquipmentType\EquipmentTypeManagement;
use Ajustatech\ServiceOrder\Livewire\EquipmentType\ShowEquipmentTypes;
use Illuminate\Support\Facades\Route;

Route::get('/ordens-servico/tipos-equipamentos', ShowEquipmentTypes::class)->name('service-order-equipment-types-show');
Route::get('/ordens-servico/tipos-equipamentos/cadastro', EquipmentTypeManagement::class)->name('service-order-equipment-types-create');
Route::get('/ordens-servico/tipos-equipamentos/{id}/editar', EquipmentTypeManagement::class)->name('service-order-equipment-types-edit');
Route::get('/ordens-servico/tipos-equipamentos/documentos/{id}/arquivo', EquipmentTypeDocumentFileController::class)->name('service-order-equipment-types-document-file');
