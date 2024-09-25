<?php

namespace Ajustatech\Financial\Database\Models;

use Ajustatech\Financial\Database\Factories\CompanyCashFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
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

    public static function createNew(array $attributes = [])
    {
        if (empty($attributes)) {
            return null;
        }

        return DB::transaction(function () use ($attributes) {
            $amount = isset($attributes['balance_amount']) ? $attributes['balance_amount'] : 0;
            $balanceDescription = isset($description['balance_description']) ? $description['balance_description'] : '';
            $cash = static::create($attributes);
            $cash->initializeBalance();
            $cash->registerInflow($amount, $balanceDescription);
            return $cash;
        });
    }

    protected function initializeBalance()
    {
        return $this->balances()->create([
            'balance' => 0,
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

    public function withDifference($difference, $is_inflow)
    {
        if (!$this->currentBalance) {
            return null;
        }


        if ($is_inflow) {
            // Entrada de caixa
            $this->currentBalance->balance += $difference;
        } else {
            // Saída de caixa
            $this->currentBalance->balance -= $difference;
        }

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
        $is_inflow = $transaction->is_inflow;
        $transaction->amount = $newAmount;
        $transaction->update();

        $diference = $newAmount - $previousAmount;
        $balance = $this->updateBalance()->withDifference($diference, $is_inflow);

        return $transaction;
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

        $amount = $data->get('amount');
        $hash = $data->get('hash');
        return  $this->registerInflow($amount, $customDescription, $hash);
    }

    public function confirmTransfer($transferData, string $customDescription = "")
    {
        if (!$this->checkTransfer($transferData)) {
            return null;
        }

        $data = collect($transferData);

        $amount = $data->get('amount');
        $hash = $data->get('hash');
        return  $this->registerOutflow($amount, $customDescription, $hash);
    }

    public function hasSufficientBalance($amount): bool
    {

        $currentBalance = $this->getBalance();
        return $currentBalance && $currentBalance->balance >= $amount;
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

        return $transaction;
    }

    public function calculateBalance()
    {
        $balance = $this->transactions()->selectRaw('SUM(CASE WHEN is_inflow = true THEN amount ELSE -amount END) as balance')->first()->balance;
        return $balance;
    }

    public function getAllTransactionsBetween($startDate, $endDate)
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
        return $this->balances()->latest()->first();
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

    protected function newHash()
    {
        return Str::uuid()->toString();
    }

    protected static function newFactory()
    {
        return CompanyCashFactory::new();
    }
}
