<?php

namespace Ajustatech\Financial\Services;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;

class CardBrandService
{
    public function listBrands()
    {
        return FinancialCardBrand::query()->orderBy('name')->get();
    }

    public function listActiveBrandNames(): array
    {
        return FinancialCardBrand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    public function create(string $name): FinancialCardBrand
    {
        return FinancialCardBrand::create([
            'name' => trim($name),
            'is_active' => true,
        ]);
    }

    public function find(string $id): FinancialCardBrand
    {
        return FinancialCardBrand::findOrFail($id);
    }

    public function update(string $id, string $name): FinancialCardBrand
    {
        $brand = $this->find($id);
        $brand->update(['name' => trim($name)]);
        return $brand->fresh();
    }

    public function delete(string $id): void
    {
        $this->find($id)->delete();
    }
}