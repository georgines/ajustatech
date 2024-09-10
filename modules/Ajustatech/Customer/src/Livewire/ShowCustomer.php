<?php

namespace Ajustatech\Customer\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\On;
use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\Core\Enums\Status;

#[Layout('core::layouts.app')]
class ShowCustomer extends Component
{
    use SwitchAlertDispatch;

    public $title = '';
    public $customers = [];
	public $search = '';
	public $activeonly = true;
	public $count = 0; //remove
	public $limiteperpage = 10;

	public function mount()
	{
        $this->title = trans('customer::messages.title');
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
        $message = "Confirma a mudança de status?";

        $this->dispatchConfirmation($message)
            ->to('change-status', id: $id)
            ->typeSuccess()
            ->setButtonOK("Sim")
            ->setButtonCancel("Não")
            ->run();
	}

	#[On('change-status')]
	public function changeStatus($id)
	{
		$customer = Customer::find($id);
		if ($customer) {
			$customer->status = $customer->status == Status::Active->value ? Status::Inactive->value  : Status::Active->value ;
			$customer->save();
			$this->searchCustomer($this->search, $this->activeonly, $this->limiteperpage);
		}
	}

    public function render()
    {
        $pageConfigs = ['myLayout' => 'vertical'];
        return view('customer::livewire.show-customer')->with([
			'customers' => $this->customers,
            'pageConfigs' => $pageConfigs
		]);
    }
}
