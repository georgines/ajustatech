<?php

namespace Ajustatech\Customer\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Ajustatech\Customer\Database\Models\Customer;
use Livewire\Attributes\Locked;
use Ajustatech\Core\Rules\CnpjValidation;
use Ajustatech\Core\Rules\CpfValidator;
use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Illuminate\Validation\Rule;

#[Layout('core::layouts.app')]
class CustomerManagement extends Component
{
    use SwitchAlertDispatch;

    #[Locked]
    public $title = 'Cadastrar Cliente';

    #[Locked]
    public $mode = 'create';

    public $customer_ = [
        'name' => '',
        'person' => 'F',
        'cpf_cnpj' => '',
        'cellphone' => '',
        'phone' => '',
        'email' => '',
        'date_of_birth' => '',
        'zip_code' => '',
        'address' => '',
        'number' => '',
        'neighborhood' => '',
        'city' => '',
        'state' => '',
        'complement' => '',
        'observations' => '',
        'status' => '1',
    ];

    public function mount(Customer $customer)
    {

        if ($customer->id) {
            $this->mode = 'edit';
            $this->title = 'Editar Cliente';
            $this->customer_ = $customer->toArray();
        } else {
            $this->mode = 'create';
            $this->title = 'Cadastrar Cliente';
            $this->resetFiels();
        }
    }

    private function create()
    {
        $customer = null;
        $this->validateData();
        $customer = Customer::create($this->customer_);
        if ($customer) {
            $message = 'Cliente salvo!';

            $this->dispatchConfirmation($message)
                ->to('redirect-to')
                ->typeSuccess()
                ->run();
        }
    }

    #[On('redirect-to')]
    public function redirectTo()
    {
        $this->redirectRoute('customers-show');
    }

    private function update()
    {
        $customer = Customer::findOrFail($this->customer_['id']);
        $this->validateData($customer->id);
        $updated = $customer->update($this->customer_);
        if ($updated) {
            $message = 'Cliente atualizado!';

            $this->dispatchConfirmation($message)
                ->to('redirect-to')
                ->typeSuccess()
                ->run();
        }
    }

    public function save()
    {
        if ($this->mode == 'create') {
            $this->create();
        } else {
            $this->update();
        }
    }

    private function resetFiels()
    {
        $this->customer_ = [
            'name' => '',
            'person' => 'F',
            'cpf_cnpj' => '',
            'cellphone' => '',
            'phone' => '',
            'email' => '',
            'date_of_birth' => '',
            'zip_code' => '',
            'address' => '',
            'number' => '',
            'neighborhood' => '',
            'city' => '',
            'state' => '',
            'complement' => '',
            'observations' => '',
            'status' => '1',
        ];
    }

    private function validateData($id = '')
    {
        $this->validate([
            'customer_.email' => 'required|email|unique:customers,email,' . $id . ',id',
            'customer_.name' => 'required|min:3',
            'customer_.cpf_cnpj' => $this->customer_['person'] == 'F' ? ['required',  Rule::unique('customers', 'cpf_cnpj')->ignore($id), 'min:11', new CpfValidator,] : ['required',  Rule::unique('customers', 'cpf_cnpj')->ignore($id), 'min:14', new CnpjValidation],
            'customer_.cellphone' => 'required|min:11',
            'customer_.date_of_birth' => 'required|date',
            'customer_.zip_code' => 'required|min:8',
            'customer_.address' => 'required|min:3',
            'customer_.neighborhood' => 'required|min:3',
            'customer_.city' => 'required|min:3',
            'customer_.state' => 'required|min:2',

        ], [], [
            'customer_.email' => 'Email',
            'customer_.name' => $this->customer_['person'] == 'F' ? 'Nome Completo' : 'Razão Social',
            'customer_.person' => 'Tipo de Pessoa',
            'customer_.cpf_cnpj' => $this->customer_['person'] == 'F' ? 'CPF' : 'CNPJ',
            'customer_.cellphone' => 'Celular',
            'customer_.phone' => 'Telefone',
            'customer_.date_of_birth' => 'Data de Nascimento',
            'customer_.zip_code' => 'CEP',
            'customer_.address' => 'Endereço',
            'customer_.number' => 'Número',
            'customer_.neighborhood' => 'Bairro',
            'customer_.city' => 'Cidade',
            'customer_.state' => 'Estado',
            'customer_.complement' => 'Complemento',
            'customer_.observations' => 'Observações',
        ]);
    }

    #[Title('clientes')]
    public function render()
    {
        return view('customer::livewire.customer-management');
    }
}
