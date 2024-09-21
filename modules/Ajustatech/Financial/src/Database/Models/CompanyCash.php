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

    const TYPE_PHYSICAL = 'physical';
    const TYPE_ONLINE = 'online';

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

    public function createNew(array $attributes = [])
    {
        $data = collect($attributes);
        if ($data->isEmpty()) {
            return null;
        }

        $cash = $this->create($attributes);
        return $this->initializeCashBalanceAndInflow($cash, $data);
    }

    protected function initializeCashBalanceAndInflow($cash, $attributes)
    {

        $amount = $attributes->has('amount') ? $attributes->get('amount') : 0;
        $description = $attributes->has('balance_description') ? $attributes->get('balance_description') : 0;

        $balance = $cash->initializeBalance($amount);

        if ($balance) {
            $cash->registerInflow($amount, $description);
        }
        return $cash;
    }

    public function calculateBalance()
    {
        $balance = $this->transactions()->selectRaw('SUM(CASE WHEN is_inflow = true THEN amount ELSE -amount END) as balance')->first()->balance;
        return $balance;
    }

    public function getAllTransactions($startDate, $endDate)
    {
        return $this->transactions()->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function initializeBalance($amount)
    {
        return $this->balances()->create([
            'balance' => $amount,
            'total_inflows' => 0,
            'total_outflows' => 0
        ]);
    }

    function applyRetroactiveTransaction($transactionId, $newAmount)
    {
        $transaction = $this->transactions()->find($transactionId);

        if (!$transaction) {
            return null;
        }

        $previousAmount = $transaction->amount;
        $transaction->amount = $newAmount;
        $transaction->update();

        $diference = $newAmount - $previousAmount;
        $balance = $this->updateCumulativeBalanceWithDifference($diference);

        return [$transaction, $balance];
    }

    protected function updateCumulativeBalanceWithDifference($difference)
    {
        $balance = $this->findLatestBalance();
        $balance->balance += $difference;
        $balance->update();
        return $balance;
    }

    public function trasfer($amount)
    {
        return $this->prepareTrasfer($amount);
    }

    protected function prepareTrasfer($amount)
    {
        $this->currentHash = $this->newHash();
        return collect([
            'amount' => $amount,
            'cash_name' => $this->cash_name,
            'id' => $this->id,
            'hash' => $this->currentHash
        ]);
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

    public function registerTransaction($amount, $is_inflow, $description = null, $hash = null)
    {
        $newHash = $hash ?: $this->newHash();
        $transaction =  $this->transactions()->create([
            'amount' => $amount,
            'description' => $description,
            'hash' => $newHash,
            'category' => null,
            'is_inflow' => $is_inflow
        ]);

        $balance = $this->updateCumulativeBalance($transaction);

        return collect([$transaction, $balance]);
    }

    protected function updateCumulativeBalance($transaction)
    {
        $data =  collect($transaction);

        if ($data->isEmpty()) {
            return null;
        }

        $is_inflow = $data->get('is_inflow');
        $balance = $this->findLatestBalance();

        $is_inflow ? $balance->total_inflows += 1 : $balance->total_outflows += 1;
        $is_inflow ? $balance->balance += $transaction->amount : $balance->balance -= $transaction->amount;
        $balance->update();
        return $balance;
    }

    protected function findLatestBalance()
    {
        return $this->balances()->latest()->first();
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
        return [self::TYPE_PHYSICAL, self::TYPE_ONLINE];
    }

    protected static function newFactory()
    {
        return CompanyCashFactory::new();
    }
}
