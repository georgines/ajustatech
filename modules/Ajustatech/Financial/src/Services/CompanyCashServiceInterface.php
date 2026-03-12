<?php

namespace Ajustatech\Financial\Services;

interface CompanyCashServiceInterface
{
    public function createCash(string $name, float $initialBalance, ?string $agency = null, ?string $account = null, ?string $description = null, bool $isOnline = true);

    public function createPhysicalCash(string $name, float $initialBalance, ?string $description = null);

    public function find(string $id);

    public static function getAllCompanyCashs();

    public function getBalance();

    public function calculateBalance();

    public function deposit(float $amount);

    public function withdrawal(float $amount);

    public function pay(float $amount, string $recipientName);

    public function toReceive(float $amount, string $customerName);

    public function bankFees(float $amount);

    public function refund(float $amount, string $transactionType);

    public function applyRetroactiveTransaction($transactionId, $newAmount);

    public static function transferBetweenCompanyCashes($amount, string $origin_cash_id, string $destination_cash_id);

}
