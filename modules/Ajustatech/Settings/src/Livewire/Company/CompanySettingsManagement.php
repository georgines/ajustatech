<?php

namespace Ajustatech\Settings\Livewire\Company;

use Ajustatech\Core\Rules\CnpjValidation;
use Ajustatech\Core\Traits\HandlesFileUploads;
use Ajustatech\Settings\Services\Company\Contracts\CompanySettingsServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class CompanySettingsManagement extends Component
{
    use HandlesFileUploads;
    use WithFileUploads;

    public string $title = '';

    #[Locked]
    public string $companySettingId = '';

    public string $companyName = '';

    public ?string $cnpj = null;

    public ?string $addressLine = null;

    public ?string $neighborhood = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $phone = null;

    public ?string $email = null;

    public mixed $logo = null;

    public string $logoAccept = '.jpg,.jpeg,.png,.webp';

    public ?string $currentLogoUrl = null;

    public ?string $temporaryLogoUrl = null;

    protected CompanySettingsServiceInterface $settingsService;

    public function boot(CompanySettingsServiceInterface $service): void
    {
        $this->settingsService = $service;
    }

    public function mount(): void
    {
        $this->title = trans('settings::messages.company_settings_edit_title');
        $this->logoAccept = $this->settingsService->acceptAttribute();

        $settings = $this->settingsService->getSettings();
        $this->companySettingId = $settings->id;
        $this->companyName = (string) $settings->company_name;
        $this->cnpj = $settings->cnpj;
        $this->addressLine = $settings->address_line;
        $this->neighborhood = $settings->neighborhood;
        $this->city = $settings->city;
        $this->state = $settings->state;
        $this->phone = $settings->phone;
        $this->email = $settings->email;
        $this->currentLogoUrl = $settings->hasLogo() ? route('settings-company-logo') : null;
    }

    protected function rules(): array
    {
        return [
            'companyName' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:20', new CnpjValidation],
            'addressLine' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function updatedLogo(): void
    {
        if (! $this->logo) {
            return;
        }

        $this->settingsService->validateLogo($this->logo);
        $this->temporaryLogoUrl = $this->temporaryUploadedFileUrl($this->logo);
        $this->resetErrorBag('logo');
    }

    public function save()
    {
        $this->validate();

        $this->settingsService->saveSettings(
            settingId: $this->companySettingId,
            payload: [
                'company_name' => $this->companyName,
                'cnpj' => $this->cnpj,
                'address_line' => $this->addressLine,
                'neighborhood' => $this->neighborhood,
                'city' => $this->city,
                'state' => $this->state,
                'phone' => $this->phone,
                'email' => $this->email,
            ],
            logo: $this->logo
        );

        $this->dispatch('settings-saved', [
            'message' => trans('settings::messages.company_settings_saved_success'),
        ]);
    }

    public function render()
    {
        return view('settings::livewire.company.company-settings-management');
    }
}
