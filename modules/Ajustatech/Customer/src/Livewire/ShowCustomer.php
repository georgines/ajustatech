<?php

namespace Ajustatech\Customer\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Ajustatech\Customer\Database\Models\Customer;

#[Layout('layouts.app')]
// #[Layout('customer::layouts.app')]
class ShowCustomer extends Component
{
    public $customers = [];
	public $search = '';
	public $activeonly = true;
	public $count = 0; //remove
	public $limiteperpage = 10;

	public function mount()
	{
		$this->customers = collect([]);
		$this->searchCustomer($this->search, $this->activeonly, $this->limiteperpage);
	}

	public function updated($prop, $value)
	{
		switch ($prop) {
			case 'search':
			case 'activeonly':
			case 'limiteperpage':
				$this->searchCustomer($this->search, $this->activeonly, $this->limiteperpage);
				break;
			default;
		}
	}

	private function searchCustomer($search, $status, $limit)
	{
		if ($search != '') {
			$this->customers = Customer::search($search, $status, $limit);
			return;
		}
		$this->customers = Customer::searchAll($status, $limit);
		return;
	}


	public function confirmChangeStatus($id)
	{
		$this->dispatch('confirm-status', id: $id);
	}

	#[On('change-status')]
	public function changeStatus($id)
	{
		$customer = Customer::find($id);
		if ($customer) {
			$customer->status = $customer->status == '1' ? '0' : '1';
			$customer->save();
			$this->searchCustomer($this->search, $this->activeonly, $this->limiteperpage);
		}
	}



	#[Title('clientes')]
    public function render()
    {
        $pageConfigs = ['myLayout' => 'vertical'];
        return view('customer::livewire.show-customer')->with([
			'customers' => $this->customers,
            'pageConfigs' => $pageConfigs
		]);
    }
}
