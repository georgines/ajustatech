<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\CompanyCashService;
use Ajustatech\Financial\Services\CompanyCashServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class CompanyCashManagement extends Component
{
    public $title;
    public $name;
    public $initialBalance;
    public $agency;
    public $account;
    public $description;
    public $isOnline = true;

    protected $companyCashService;

    public function mount()
    {
        $this->title = trans('financial::messages.company_cash_create_title');

    }

    public function updatedIsOnline($value)
    {
        if (!$value) {
            // Limpar agência e conta se o caixa não for online
            $this->agency = null;
            $this->account = null;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'initialBalance' => 'required|numeric|min:0',
            'agency' => 'nullable|string|max:50',
            'account' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $this->companyCashService = app(CompanyCashServiceInterface::class);

        $this->companyCashService->createCash(
            $this->name,
            $this->initialBalance,
            $this->isOnline ? $this->agency : null,
            $this->isOnline ? $this->account : null,
            $this->description,
            $this->isOnline
        );

        // session()->flash('success', trans('financial::messages.company_cash_created_success'));
        return redirect()->route('companycash-show');
    }

    public function render()
    {
        return view('financial::livewire.company-cash-management');
    }
}
