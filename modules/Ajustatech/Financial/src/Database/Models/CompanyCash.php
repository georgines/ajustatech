<?php

namespace Ajustatech\Financial\Database\Models;

use Ajustatech\Financial\Database\Factories\CompanyCashFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CompanyCash extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'user_name',
        'cash_name',
        'description',
        'agency',
        'account',
        'is_online',
        'is_active'
    ];

    protected $currentHash;

    public function balances(): HasMany
    {
        return $this->hasMany(CompanyCashBalances::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CompanyCashTransactions::class);
    }

    public function registerTransaction($amount, $is_inflow, $description = null, $hash = null)
    {
        $newHash = $hash ?: $this->newHash();
        return $this->transactions()->create([
            'amount' => $amount,
            'description' => $description,
            'hash' => $newHash,
            'category',
            'is_inflow' => $is_inflow
        ]);
    }

    public function calculateBalance()
    {
        $transactions = $this->cash->transactions;
        $balance = $transactions->sum(function ($transactions) {
            return $transactions->is_inflow ? $transactions->amount : -$transactions->amount;
        });
        return $balance;
    }

    public function registerInflow($amount, $description = null, $hash = null)
    {
        return $this->registerTransaction($amount, true, $description, $hash);
    }

    public function registerOutflow($amount, $description = null, $hash = null)
    {
        return $this->registerTransaction($amount, false, $description, $hash);
    }

    function revertTransaction($id)
    {
        $transaction = $this->transactions()->find($id);
        if ($transaction) {
            $this->registerTransaction(
                $transaction->amount,
                !$transaction->is_inflow,
                $transaction->description,
                $this->newHash()
            );
        }
        return null;
    }

    public function trasfer($amount)
    {
        $this->currentHash = $this->newHash();
        return collect(['amount' => $amount, 'cash_name' => $this->cash_name, 'id' => $this->id, 'hash' => $this->currentHash]);
    }

    public function receive($transferData, string $customDescription = "")
    {
        $data = collect($transferData);

        if ($data->isEmpty()) {
            return null;
        }

        $placeholders = [
            ':amount' => $data->get('amount'),
            ':originCashName' => $data->get('cash_name'),
            ':originCashId' => $data->get('id'),
            ':transferHash' => $data->get('hash'),
            ':destinationCashName' => $this->cash_name,
            ':destinationCashId' => $this->id
        ];

        $description = $this->replacePlaceholders($customDescription, $placeholders);

        $inflowResult = $this->registerInflow($placeholders[':amount'], $description, $placeholders[':transferHash']);

        return collect($inflowResult)
            ->put('destination_cash_name', $this->cash_name)
            ->put('destination_cash_id', $this->id);
    }

    public function confirmTransfer($transferData, string $customDescription = "")
    {

        if (!$this->checkTransfer($transferData)) {
            return null;
        }

        $data = collect($transferData);

        $placeholders = [
            ':amount' => $data->get('amount'),
            ':destinationCashName' => $data->get('destination_cash_name'),
            ':destinationCashId' => $data->get('destination_cash_id'),
            ':transferHash' => $data->get('hash')
        ];

        $description = $this->replacePlaceholders($customDescription, $placeholders);

        $outflowResult = $this->registerOutflow($placeholders[':amount'], $description, $placeholders[':transferHash']);
        return collect($outflowResult);
    }

    protected function checkTransfer($transferData)
    {
        $data = collect($transferData);
        if ($data->isEmpty()) {
            return false;
        }
        return $this->currentHash === $data->get('hash');
    }

    protected function replacePlaceholders(string $descriptionTemplate, array $placeholders): string
    {
        return strtr($descriptionTemplate, $placeholders);
    }

    protected function newHash()
    {
        return Str::uuid()->toString();
    }

    public function availableCompanyCashsTypes(): array
    {
        return [
            'physical',
            'online',
        ];
    }

    protected static function newFactory()
    {
        return CompanyCashFactory::new();
    }
}
