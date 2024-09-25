<?php

namespace Ajustatech\Financial\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Exceptions\InsufficientBalanceException;

class CompanyCashService implements CompanyCashServiceInterface
{
    protected $cash;
    protected $userId = null;
    protected $userName = null;

    public function __construct(CompanyCash $cash)
    {
        $this->cash = $cash;
    }

    public function createNewCash(string $name, float $initialBalance, ?string $agency = null, ?string $account = null, ?string $description = null, bool $isOnline = true)
    {
        $this->cash = CompanyCash::createNew([
            'cash_name' => $name,
            'description' => $description,
            'agency' => $agency,
            'account' => $account,
            'balance_amount' => $initialBalance,
            'balance_description' => $this->getInitialDepositDescription($initialBalance),
            'is_online' => $isOnline,
            'is_active' => true
        ]);
        return $this->cash;
    }

    public function createNewPhysicalCash(string $name, float $initialBalance, ?string $description = null)
    {
        return $this->createNewCash($name, $initialBalance, null, null, $description, false);
    }

    private function getInitialDepositDescription(float $initialBalance): string
    {
        return trans('transactions.initial_deposit', ['amount' => $initialBalance]);
    }

    public function find(string $id)
    {
        $this->cash = CompanyCash::findOrFail($id);
        return $this->cash;
    }

    public function getBalance()
    {
        return $this->cash->getBalance();
    }

    public function calculateBalance()
    {
        return $this->cash->calculateBalance();
    }

    public function deposit(float $amount)
    {
        $description = trans('transactions.deposit', ['amount' => $amount]);
        return $this->cash->registerInflow($amount, $description);
    }

    public function withdrawal(float $amount)
    {
        $this->ensureSufficientBalance($amount);
        $description = trans('transactions.withdrawal', ['amount' => $amount]);
        return $this->cash->registerOutflow($amount, $description);
    }

    public function pay(float $amount, string $recipientName)
    {
        $this->ensureSufficientBalance($amount);
        $description = trans('transactions.payment', ['amount' => $amount, 'recipient_name' => $recipientName]);
        return $this->cash->registerOutflow($amount, $description);
    }

    public function toReceive(float $amount, string $customerName)
    {
        $description = trans('transactions.receipt', ['amount' => $amount, 'payer_name' => $customerName]);
        return $this->cash->registerInflow($amount, $description);
    }

    public function bankFees(float $amount)
    {
        $this->ensureSufficientBalance($amount);
        $description = trans('transactions.bank_fees', ['amount' => $amount]);
        return $this->cash->registerOutflow($amount, $description);
    }

    public function refund(float $amount, string $transactionType)
    {
        $description = trans('transactions.refund', ['amount' => $amount, 'transaction_type' => $transactionType]);
        return $this->cash->registerInflow($amount, $description);
    }

    public function applyRetroactiveTransaction($transactionId, $newAmount)
    {
        return $this->cash->applyRetroactiveTransaction($transactionId, $newAmount);
    }

    public static function transferBetweenCompanyCashes($amount, string $origin_cash_id, string $destination_cash_id)
    {
        $originCompanyCash = CompanyCash::find($origin_cash_id);
        $destinationCompanyCash = CompanyCash::find($destination_cash_id);

        $transferData = $originCompanyCash->transfer($amount);

        $description1 = trans('transactions.transfer_received', [
            'amount' => $amount,
            'originCashName' => $destinationCompanyCash->cash_name,
            'originCashId' => $destinationCompanyCash->id,
            'transferHash' => $transferData->get("hash")
        ]);

        $description2 = trans('transactions.transfer_sent', [
            'amount' => $amount,
            'destinationCashName' => $originCompanyCash->cash_name,
            'destinationCashId' => $originCompanyCash->id,
            'transferHash' => $transferData->get("hash")
        ]);

        $receiptDestination = $destinationCompanyCash->receive($transferData, $description1);
        $originCompanyCashConfirmation = $originCompanyCash->confirmTransfer($transferData, $description2);
        return $originCompanyCashConfirmation;
    }

    public function getAllTransactionsBetween($startDate, $endDate)
    {
        return $this->cash->getAllTransactionsBetween($startDate, $endDate);
    }

    protected function ensureSufficientBalance(float $amount)
    {
        $balance = $this->cash->getBalance();
        if (!$balance || $balance->balance < $amount) {
            throw new InsufficientBalanceException($amount, $balance ? $balance->balance : 0);
        }
    }
}
