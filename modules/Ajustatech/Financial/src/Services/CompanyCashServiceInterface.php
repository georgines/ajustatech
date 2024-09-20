<?php

namespace Ajustatech\Financial\Services;

interface CompanyCashServiceInterface
{
    public function createCash(string $name);
    public function getAllCashs();
    public function getCash(string $id);
    public function deposit(float $amount, string $description = null): void;
    public function withdraw(float $amount, string $description = null): void;
    public function transfer($destinationCashId, float $amount, string $description = null): void;
    public function pay(float $amount, string $description = null): void;
}
