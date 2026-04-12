<?php

namespace Ajustatech\Settings\Livewire\Company;

use Ajustatech\Core\Rules\CnpjValidation;
use Ajustatech\Core\Traits\HandlesFileUploads;
use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Ajustatech\Settings\Services\Company\Contracts\CompanySettingsServiceInterface;
use Illuminate\Http\UploadedFile;
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
        $this->currentLogoUrl = $this->buildCurrentLogoUrl($settings);
    }

    protected function rules(): array
    {
        return [
            'companyName' => ['required', 'string', 'max:255'],
            'cnpj' => ['required', 'string', 'max:20', new CnpjValidation],
            'addressLine' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'size:2'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'companyName' => trans('settings::messages.company_name_label'),
            'cnpj' => trans('settings::messages.company_cnpj_label'),
            'addressLine' => trans('settings::messages.company_address_label'),
            'neighborhood' => trans('settings::messages.company_neighborhood_label'),
            'city' => trans('settings::messages.company_city_label'),
            'state' => trans('settings::messages.company_state_label'),
            'phone' => trans('settings::messages.company_phone_label'),
            'email' => trans('settings::messages.company_email_label'),
            'logo' => trans('settings::messages.company_logo_label'),
        ];
    }

    public function updatedLogo(): void
    {
        if (! $this->logo) {
            $this->temporaryLogoUrl = null;

            return;
        }

        $this->settingsService->validateLogo($this->logo);
        $this->temporaryLogoUrl = $this->temporaryUploadedFileUrl($this->logo);
        $this->resetErrorBag('logo');
    }

    public function save()
    {
        $this->validate($this->rules(), [], $this->validationAttributes());
        $logo = $this->selectedLogoForSave();
        /** @var CompanySetting $updatedSettings */
        $updatedSettings = $this->settingsService->saveSettings(
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
            logo: $logo
        );

        $this->currentLogoUrl = $this->buildCurrentLogoUrl($updatedSettings);
        $this->temporaryLogoUrl = null;
        $this->logo = null;

        $this->dispatch('settings-saved', [
            'message' => trans('settings::messages.company_settings_saved_success'),
        ]);
    }

    public function render()
    {
        return view('settings::livewire.company.company-settings-management');
    }

    private function selectedLogoForSave(): mixed
    {
        if ($this->logo instanceof UploadedFile) {
            return $this->logo;
        }

        return null;
    }

    private function buildCurrentLogoUrl(CompanySetting $settings): ?string
    {
        if (! $settings->hasLogo()) {
            return null;
        }

        return route('settings-company-logo', [
            'v' => sha1((string) $settings->logo_path),
        ]);
    }
}
