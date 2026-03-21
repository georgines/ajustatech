<?php

namespace Ajustatech\Financial\Services;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentMethodService
{
    public function createMethodWithCost(string $type, string $name, array $cost): FinancialPaymentMethod
    {
        return $this->createMethodWithCosts($type, $name, [$cost]);
    }

    public function createMethodWithCosts(string $type, string $name, array $costs): FinancialPaymentMethod
    {
        $this->ensureValidType($type);

        if (empty($costs)) {
            throw new InvalidArgumentException(trans('financial::messages.payment_method_cost_required'));
        }

        return DB::transaction(function () use ($type, $name, $costs) {
            $method = FinancialPaymentMethod::create([
                'type' => $type,
                'name' => $name,
                'is_active' => true,
            ]);

            foreach ($costs as $cost) {
                $this->validateCostRule($type, $cost);
                $method->costs()->create([
                    'fixed_cost' => (float) ($cost['fixed_cost'] ?? 0),
                    'percent_cost' => (float) ($cost['percent_cost'] ?? 0),
                    'brand' => $cost['brand'] ?? null,
                    'installments' => isset($cost['installments']) ? (int) $cost['installments'] : null,
                    'receipt_channel' => $cost['receipt_channel'] ?? null,
                ]);
            }

            return $method->fresh('costs');
        });
    }

    public function listMethods()
    {
        return FinancialPaymentMethod::query()->with('costs')->latest()->get();
    }

    public function findMethod(string $id): FinancialPaymentMethod
    {
        return FinancialPaymentMethod::query()->with('costs')->findOrFail($id);
    }

    public function updateMethodWithCosts(string $id, string $type, string $name, array $costs): FinancialPaymentMethod
    {
        $this->ensureValidType($type);

        if (empty($costs)) {
            throw new InvalidArgumentException(trans('financial::messages.payment_method_cost_required'));
        }

        return DB::transaction(function () use ($id, $type, $name, $costs) {
            $method = FinancialPaymentMethod::findOrFail($id);

            $method->update([
                'type' => $type,
                'name' => $name,
            ]);

            $method->costs()->delete();

            foreach ($costs as $cost) {
                $this->validateCostRule($type, $cost);
                $method->costs()->create([
                    'fixed_cost' => (float) ($cost['fixed_cost'] ?? 0),
                    'percent_cost' => (float) ($cost['percent_cost'] ?? 0),
                    'brand' => $cost['brand'] ?? null,
                    'installments' => isset($cost['installments']) ? (int) $cost['installments'] : null,
                    'receipt_channel' => $cost['receipt_channel'] ?? null,
                ]);
            }

            return $method->fresh('costs');
        });
    }

    public function deleteMethod(string $id): void
    {
        $method = FinancialPaymentMethod::findOrFail($id);
        $method->delete();
    }

    public function getTypes(): array
    {
        return [
            FinancialPaymentMethod::TYPE_DINHEIRO,
            FinancialPaymentMethod::TYPE_PIX,
            FinancialPaymentMethod::TYPE_CARTAO_DEBITO,
            FinancialPaymentMethod::TYPE_CARTAO_CREDITO,
        ];
    }

    public function getReceiptChannels(): array
    {
        return ['maquina', 'telefone', 'link'];
    }

    private function ensureValidType(string $type): void
    {
        if (!in_array($type, $this->getTypes(), true)) {
            throw new InvalidArgumentException(trans('financial::messages.payment_method_invalid_type'));
        }
    }

    private function validateCostRule(string $type, array $cost): void
    {
        $isCard = in_array($type, [
            FinancialPaymentMethod::TYPE_CARTAO_DEBITO,
            FinancialPaymentMethod::TYPE_CARTAO_CREDITO,
        ], true);

        if ($isCard) {
            if (empty($cost['brand']) || empty($cost['installments']) || empty($cost['receipt_channel'])) {
                throw new InvalidArgumentException(trans('financial::messages.payment_method_card_rule_required'));
            }

            $brandExists = FinancialCardBrand::query()->where('name', $cost['brand'])->where('is_active', true)->exists();
            if (!$brandExists) {
                throw new InvalidArgumentException(trans('financial::messages.payment_method_invalid_brand'));
            }

            if (!in_array($cost['receipt_channel'], $this->getReceiptChannels(), true)) {
                throw new InvalidArgumentException(trans('financial::messages.payment_method_invalid_receipt_channel'));
            }
        }
    }
}
