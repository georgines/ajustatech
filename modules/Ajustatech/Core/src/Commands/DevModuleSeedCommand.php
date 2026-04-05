<?php

namespace Ajustatech\Core\Commands;

class DevModuleSeedCommand extends BaseCommand
{
    protected $signature = 'module:seed';
    protected $description = 'Seed specific modules';

    public function handle(): int
    {
        if ($this->executeCommand('module:seed-customer') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-company-cash-transactions') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-company-cash-balances') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-company-cash') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-financial-card-brands') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-financial-payment-methods') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-financial-receivables') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-financial-payables') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-financial-cash-flow-routes') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-sales-cash-sessions') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->executeCommand('module:seed-service-order') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->commandExists('module:seed-service-order-old')) {
            if ($this->executeCommand('module:seed-service-order-old') !== self::SUCCESS) {
                return self::FAILURE;
            }
        } else {
            $this->warn('Skipping optional command: module:seed-service-order-old (not registered).');
        }

        $this->info('All module seed commands were executed.');

        return self::SUCCESS;
    }

    private function executeCommand(string $command): int
    {
        $this->info("Running command: {$command}");
        $exitCode = $this->call($command);

        if ($exitCode !== self::SUCCESS) {
            $this->error("Command failed: {$command}");
        }

        return $exitCode;
    }

    private function commandExists(string $command): bool
    {
        return $this->getApplication()?->has($command) ?? false;
    }
}
