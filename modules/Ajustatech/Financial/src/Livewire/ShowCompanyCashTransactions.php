<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\CompanyCashServiceInterface;
use Ajustatech\Financial\Services\CompanyCashTransactionsServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Carbon;

#[Layout('core::layouts.app')]
class ShowCompanyCashTransactions extends Component
{
    public $title;
    public $companyCashId;
    public $transactions = [];
    public $balance = null;
    public $offset = 0;
    public $limit = 2;
    public $startDate = null;
    public $endDate = null;

    protected $listeners = ['loadMore'];

    public function mount($id)
    {
        $this->companyCashId = $id;
        $this->loadTransactions(); // Carrega as primeiras 10 transações ao iniciar o componente
        $this->title = trans('financial::messages.title');
    }

    public function loadTransactions()
    {
        $transactionsService = app(CompanyCashTransactionsServiceInterface::class);
        $cash = app(CompanyCashServiceInterface::class);

        $transactions = $transactionsService->getAllTransactionsBetween(
            $this->companyCashId,
            $this->startDate,
            $this->endDate,
            $this->offset,
            $this->limit
        );

        $cash->find($this->companyCashId);
        $this->balance = $cash->getBalance()->balance;


        $this->transactions = array_merge($this->transactions, $transactions);
        $this->offset += $this->limit;
    }

    public function loadMore()
    {
        $this->loadTransactions();
    }

    public function loadLast7Days()
    {
        $this->setDateRange(Carbon::today()->subDays(7), Carbon::now());
    }

    public function loadLastMonth()
    {
        $this->setDateRange(Carbon::today()->subMonth(), Carbon::now());
    }

    public function loadLastYear()
    {
        $this->setDateRange(Carbon::today()->subYear(), Carbon::now());
    }

    public function loadCustomInterval($startDate, $endDate)
    {
        $this->setDateRange(Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay());
    }

    private function setDateRange($startDate, $endDate)
    {
        $this->resetPagination();
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->loadTransactions();
    }

    private function resetPagination()
    {
        $this->transactions = [];
        $this->offset = 0;
    }

    public function render()
    {
        return view('financial::livewire.show-company-cash-transactions');
    }
}
