<?php

namespace Ajustatech\Financial\Tests\Feature\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Services\CompanyCashTransactionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CompanyCashTransactionsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompanyCashTransactionsService $transactionsService;
    protected CompanyCash $companyCash;

    protected function setUp(): void
    {
        parent::setUp();

        // Criando um exemplo de caixa manualmente e instanciando o serviço
        $this->companyCash = CompanyCash::create([
            'cash_name' => 'Main Cash',
            'balance_amount' => 1000,
            'is_online' => true,
            'is_active' => true,
        ]);

        $this->transactionsService = new CompanyCashTransactionsService($this->companyCash);
    }

    public function test_get_all_transactions_between_dates_should_return_correct_transactions()
    {
        // Criando transações usando os métodos da classe CompanyCash
        $transaction1 = $this->companyCash->registerInflow(500, 'Initial inflow');
        $transaction2 = $this->companyCash->registerInflow(300, 'Second inflow');

        // Atualizando as datas diretamente nas transações
        $transaction1->created_at = Carbon::parse('2024-01-01');
        $transaction1->save();

        $transaction2->created_at = Carbon::parse('2024-02-01');
        $transaction2->save();

        // Recuperando transações entre as datas 2024-01-01 e 2024-03-01
        $transactions = $this->transactionsService->getAllTransactionsBetween(
            $this->companyCash->id,
            '2024-01-01',
            '2024-03-01',
            0,
            10
        );

        $this->assertCount(2, $transactions);
        $this->assertEquals('2024-02-01', Carbon::parse($transactions[0]['created_at'])->format('Y-m-d'));
        $this->assertEquals('2024-01-01', Carbon::parse($transactions[1]['created_at'])->format('Y-m-d'));
    }

    public function test_get_all_transactions_between_dates_should_return_no_transactions_if_none_exist_in_range()
    {
        // Criando transações fora do intervalo
        $transaction1 = $this->companyCash->registerInflow(500, 'Old inflow');
        $transaction2 = $this->companyCash->registerInflow(300, 'Future inflow');

        // Atualizando as datas diretamente nas transações
        $transaction1->created_at = Carbon::parse('2023-01-01');
        $transaction1->save();

        $transaction2->created_at = Carbon::parse('2025-01-01');
        $transaction2->save();

        // Recuperando transações entre as datas 2024-01-01 e 2024-03-01
        $transactions = $this->transactionsService->getAllTransactionsBetween(
            $this->companyCash->id,
            '2024-01-01',
            '2024-03-01',
            0,
            10
        );

        // Deve retornar 0 transações, já que nenhuma transação corresponde ao intervalo
        $this->assertCount(0, $transactions);
    }

    public function test_get_all_transactions_between_dates_should_not_return_transactions_out_of_range()
    {
        // Criando transações com datas fora do intervalo
        $transaction1 = $this->companyCash->registerInflow(500, 'Early inflow');
        $transaction2 = $this->companyCash->registerInflow(300, 'Late inflow');

        // Atualizando as datas diretamente nas transações
        $transaction1->created_at = Carbon::parse('2023-12-31');
        $transaction1->save();

        $transaction2->created_at = Carbon::parse('2024-04-01');
        $transaction2->save();

        // Criando uma transação dentro do intervalo
        $transaction3 = $this->companyCash->registerInflow(400, 'In-range inflow');
        $transaction3->created_at = Carbon::parse('2024-02-15');
        $transaction3->save();

        // Recuperando transações entre as datas 2024-01-01 e 2024-03-01
        $transactions = $this->transactionsService->getAllTransactionsBetween(
            $this->companyCash->id,
            '2024-01-01',
            '2024-03-01',
            0,
            10
        );

        // Deve retornar apenas 1 transação que está dentro do intervalo
        $this->assertCount(1, $transactions);
        $this->assertEquals('2024-02-15', Carbon::parse($transactions[0]['created_at'])->format('Y-m-d'));
    }

    public function test_get_all_transactions_between_dates_should_include_transactions_at_the_boundary()
    {
        // Criando transações exatamente nos limites do intervalo
        $transaction1 = $this->companyCash->registerInflow(500, 'Start boundary inflow');
        $transaction2 = $this->companyCash->registerInflow(300, 'End boundary inflow');

        // Atualizando as datas diretamente nas transações
        $transaction1->created_at = Carbon::parse('2024-01-01');
        $transaction1->save();

        $transaction2->created_at = Carbon::parse('2024-03-01');
        $transaction2->save();

        // Recuperando transações entre as datas 2024-01-01 e 2024-03-01
        $transactions = $this->transactionsService->getAllTransactionsBetween(
            $this->companyCash->id,
            '2024-01-01',
            '2024-03-01',
            0,
            10
        );

        // Deve retornar ambas as transações que estão exatamente nos limites do intervalo
        $this->assertCount(2, $transactions);
        $this->assertEquals('2024-03-01', Carbon::parse($transactions[0]['created_at'])->format('Y-m-d'));
        $this->assertEquals('2024-01-01', Carbon::parse($transactions[1]['created_at'])->format('Y-m-d'));
    }


    public function test_get_all_transactions_between_dates_with_pagination()
    {
        // Criando 15 transações manualmente para testar paginação
        $this->companyCash->registerInflow(100, 'Transaction 1', Carbon::now()->subDays(0));
        $this->companyCash->registerInflow(100, 'Transaction 2', Carbon::now()->subDays(1));
        $this->companyCash->registerInflow(100, 'Transaction 3', Carbon::now()->subDays(2));
        $this->companyCash->registerInflow(100, 'Transaction 4', Carbon::now()->subDays(3));
        $this->companyCash->registerInflow(100, 'Transaction 5', Carbon::now()->subDays(4));
        $this->companyCash->registerInflow(100, 'Transaction 6', Carbon::now()->subDays(5));
        $this->companyCash->registerInflow(100, 'Transaction 7', Carbon::now()->subDays(6));
        $this->companyCash->registerInflow(100, 'Transaction 8', Carbon::now()->subDays(7));
        $this->companyCash->registerInflow(100, 'Transaction 9', Carbon::now()->subDays(8));
        $this->companyCash->registerInflow(100, 'Transaction 10', Carbon::now()->subDays(9));
        $this->companyCash->registerInflow(100, 'Transaction 11', Carbon::now()->subDays(10));
        $this->companyCash->registerInflow(100, 'Transaction 12', Carbon::now()->subDays(11));
        $this->companyCash->registerInflow(100, 'Transaction 13', Carbon::now()->subDays(12));
        $this->companyCash->registerInflow(100, 'Transaction 14', Carbon::now()->subDays(13));
        $this->companyCash->registerInflow(100, 'Transaction 15', Carbon::now()->subDays(14));

        // Recuperando transações com limite de 10 e offset de 0 (primeira página)
        $transactionsPage1 = $this->transactionsService->getAllTransactionsBetween(
            $this->companyCash->id,
            null,
            null,
            0,
            10
        );

        // Recuperando transações com limite de 10 e offset de 10 (segunda página)
        $transactionsPage2 = $this->transactionsService->getAllTransactionsBetween(
            $this->companyCash->id,
            null,
            null,
            10,
            10
        );

        $this->assertCount(10, $transactionsPage1);
        $this->assertCount(5, $transactionsPage2); // Restantes das 15 transações
    }

    public function test_get_all_transactions_with_no_date_range_should_return_all_transactions()
    {
        // Criando 3 transações manualmente usando os métodos da classe CompanyCash
        $this->companyCash->registerInflow(200, 'Transaction 1', Carbon::now());
        $this->companyCash->registerInflow(300, 'Transaction 2', Carbon::now());
        $this->companyCash->registerInflow(400, 'Transaction 3', Carbon::now());

        // Recuperando todas as transações sem filtro de data
        $transactions = $this->transactionsService->getAllTransactionsBetween(
            $this->companyCash->id,
            null,
            null,
            0,
            10
        );

        $this->assertCount(3, $transactions);
    }

    public function test_get_all_transactions_with_non_existing_cash_should_throw_exception()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Tentativa de recuperar transações para um ID de caixa que não existe
        $this->transactionsService->getAllTransactionsBetween(
            'invalid_id',
            null,
            null,
            0,
            10
        );
    }
}
