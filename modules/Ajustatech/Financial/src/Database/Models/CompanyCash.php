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
    const INFLOW_INCREMENT = 1;
    const OUTFLOW_INCREMENT = 1;

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
    protected $currentBalance;

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
        $data = $this->collectIfNotEmpty($attributes);
        if (!$data) {
            return null;
        }

        $cash = $this->create($attributes);
        return $this->initializeCashBalanceAndInflow($cash, $data);
    }

    public function initializeBalance($amount)
    {
        return $this->balances()->create([
            'balance' => $amount,
            'total_inflows' => 0,
            'total_outflows' => 0
        ]);
    }

    public function getBalance()
    {
        $this->currentBalance = $this->findLatestBalance();
        return $this->currentBalance;
    }

    public function updateBalance()
    {
        $this->getBalance();
        return $this;
    }

    public function withDifference($difference)
    {
        if (!$this->currentBalance) {
            return null;
        }

        $this->currentBalance->balance += $difference;
        $this->currentBalance->update();
        return $this->currentBalance;
    }

    public function withAmount($amount, $is_inflow)
    {
        if (!$this->currentBalance) {
            return null;
        }

        if ($is_inflow) {
            $this->currentBalance->total_inflows += self::INFLOW_INCREMENT;
            $this->currentBalance->balance += $amount;
        } else {
            $this->currentBalance->total_outflows += self::OUTFLOW_INCREMENT;
            $this->currentBalance->balance -= $amount;
        }

        $this->currentBalance->update();
        return $this->currentBalance;
    }

    public function applyRetroactiveTransaction($transactionId, $newAmount)
    {
        $transaction = $this->transactions()->find($transactionId);

        if (!$transaction) {
            return null;
        }

        $previousAmount = $transaction->amount;
        $transaction->amount = $newAmount;
        $transaction->update();

        $diference = $newAmount - $previousAmount;
        $balance = $this->updateBalance()->withDifference($diference);

        return [$transaction, $balance];
    }

    public function transfer($amount)
    {
        return $this->prepareTransfer($amount);
    }

    public function receive($transferData, string $customDescription = "")
    {
        $data = $this->collectIfNotEmpty($transferData);
        if (!$data) {
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

        return  $this->registerOutflow($placeholders[':amount'], $description, $placeholders[':transferHash']);
    }

    public function registerInflow($amount, $description = null, $hash = null)
    {
        return $this->registerTransaction($amount, true, $description, $hash);
    }

    public function registerOutflow($amount, $description = null, $hash = null)
    {
        return $this->registerTransaction($amount, false, $description, $hash);
    }

    protected function registerTransaction($amount, $is_inflow, $description = null, $hash = null)
    {
        $newHash = $hash ?: $this->newHash();
        $transaction =  $this->transactions()->create([
            'amount' => $amount,
            'description' => $description,
            'hash' => $newHash,
            'category' => null,
            'is_inflow' => $is_inflow
        ]);

        $balance = $this->updateBalance()->withAmount($amount, $is_inflow);
        return collect([$transaction, $balance]);
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

    public function availableCompanyCashsTypes(): array
    {
        return [self::TYPE_PHYSICAL, self::TYPE_ONLINE];
    }

    protected function initializeCashBalanceAndInflow($cash, array  $attributes)
    {
        $amount = isset($attributes['amount']) ? $attributes['amount'] : 0;
        $description = isset($attributes['balance_description']) ? $attributes['balance_description'] : 0;

        $balance = $cash->initializeBalance($amount);

        if ($balance) {
            $cash->registerInflow($amount, $description);
        }
        return $cash;
    }

    protected function findLatestBalance()
    {
        return $this->balances()->latest()->first() ?? null;
    }

    protected function prepareTransfer($amount)
    {
        $this->currentHash = $this->newHash();
        return collect([
            'amount' => $amount,
            'cash_name' => $this->cash_name,
            'id' => $this->id,
            'hash' => $this->currentHash
        ]);
    }

    protected function checkTransfer($transferData)
    {
        $data = $this->collectIfNotEmpty($transferData);
        if (!$data) {
            return false;
        }
        return $this->isValidHash($data->get('hash'));
    }

    protected function isValidHash($hash)
    {
        return $this->currentHash === $hash;
    }

    protected function collectIfNotEmpty($data)
    {
        $collection = collect($data);
        return $collection->isEmpty() ? null : $collection;
    }

    protected function replacePlaceholders(string $descriptionTemplate, array $placeholders): string
    {
        return strtr($descriptionTemplate, $placeholders);
    }

    protected function newHash()
    {
        return Str::uuid()->toString();
    }

    protected static function newFactory()
    {
        return CompanyCashFactory::new();
    }
}
