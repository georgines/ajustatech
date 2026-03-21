<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Core\Traits\HandlesCompanyCashTransfer;
use Ajustatech\Financial\Exceptions\InsufficientBalanceException;
use Ajustatech\Financial\Services\CompanyCashServiceInterface;
use Ajustatech\Financial\Services\CompanyCashTransactionsServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowCompanyCashTransactions extends Component
{
    use HandlesCompanyCashTransfer;

    public $title;
    public $companyCashId;
    public $transactions = [];
    public $balance = null;
    public $destinationCashId = '';
    public $transferAmount = null;
    public $availableCashes = [];
    public $offset = 0;
    public $limit = 2;
    public $startDate = null;
    public $endDate = null;

    protected $listeners = ['loadMore'];

    public function mount($id)
    {
        $this->companyCashId = $id;
        $this->loadTransactions();
        $this->loadAvailableCashes();
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

    public function transferToAnotherCash(): void
    {
        $this->validate(
            $this->transferValidationRules($this->companyCashId),
            [],
            $this->transferValidationAttributes()
        );

        try {
            $cashService = app(CompanyCashServiceInterface::class);
            $cashService::transferBetweenCompanyCashes(
                (float) $this->transferAmount,
                $this->companyCashId,
                $this->destinationCashId
            );

            $this->resetPagination();
            $this->loadTransactions();
            $this->resetTransferForm();
            $this->dispatch('cash-transfer-success', ['message' => trans('financial::messages.transfer_success')]);
        } catch (InsufficientBalanceException $exception) {
            $this->addError('transferAmount', trans('financial::messages.transfer_insufficient_balance'));
        } catch (InvalidArgumentException | ModelNotFoundException $exception) {
            $this->addError('destinationCashId', $exception->getMessage());
        }
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

    private function loadAvailableCashes(): void
    {
        $cashService = app(CompanyCashServiceInterface::class);
        $this->availableCashes = $cashService::getAllCompanyCashs()
            ->where('id', '!=', $this->companyCashId)
            ->values()
            ->all();
    }

    public function render()
    {
        return view('financial::livewire.show-company-cash-transactions');
    }
}