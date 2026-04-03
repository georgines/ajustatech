<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogServiceStep;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ServiceCatalogManagement extends Component
{
    public string $title = 'Cadastro de Servico';
    public string $mode = 'create';
    public ?string $serviceId = null;
    public bool $embedded = false;

    public string $name = '';
    public ?string $description = null;
    public float $base_price = 0.0;
    public bool $is_active = true;
    public bool $is_reusable = true;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $steps = [];

    public function mount(?string $id = null, bool $embedded = false): void
    {
        $this->embedded = $embedded;

        if ($id) {
            $this->loadService($id);
            return;
        }

        $this->bootNewForm();
    }

    public function addStep(): void
    {
        if (!$this->canAppendStep()) {
            $this->addError('steps', 'Preencha o procedimento atual antes de adicionar um novo.');
            return;
        }

        $this->resetErrorBag('steps');
        $this->steps[] = $this->makeEmptyStep(count($this->steps) + 1);
    }

    public function removeStep(int $index): void
    {
        if (!array_key_exists($index, $this->steps)) {
            return;
        }

        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);

        if (empty($this->steps)) {
            $this->steps[] = $this->makeEmptyStep(1);
        }

        $this->reindexSteps();
    }

    public function moveStepUp(int $index): void
    {
        if ($index <= 0 || !array_key_exists($index, $this->steps)) {
            return;
        }

        $target = $index - 1;
        [$this->steps[$target], $this->steps[$index]] = [$this->steps[$index], $this->steps[$target]];
        $this->reindexSteps();
    }

    public function moveStepDown(int $index): void
    {
        if (!array_key_exists($index, $this->steps)) {
            return;
        }

        $target = $index + 1;
        if (!array_key_exists($target, $this->steps)) {
            return;
        }

        [$this->steps[$index], $this->steps[$target]] = [$this->steps[$target], $this->steps[$index]];
        $this->reindexSteps();
    }

    public function save()
    {
        $validated = $this->validate($this->rules(), $this->messages());

        DB::transaction(function () use ($validated): void {
            $servicePayload = Arr::only($validated, ['name', 'description', 'base_price', 'is_active', 'is_reusable']);
            $servicePayload['name'] = trim((string) $servicePayload['name']);
            $servicePayload['description'] = $this->normalizeNullableText($servicePayload['description'] ?? null);

            if ($this->mode === 'edit' && $this->serviceId) {
                $service = ServiceCatalogService::findOrFailById($this->serviceId);
                $service->updateFromPayload($servicePayload);
            } else {
                $service = ServiceCatalogService::createFromPayload($servicePayload);
                $this->mode = 'edit';
                $this->serviceId = $service->id;
                $this->title = 'Editar Servico';
            }

            $service->replaceSteps($this->normalizedStepsForPersistence());
        });

        if ($this->embedded) {
            $this->dispatch('service-catalog-changed');
            $this->bootNewForm();
            return null;
        }

        return redirect()->route('service-order-services-show');
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'steps.')) {
            $this->resetErrorBag('steps');
        }
    }

    public function render()
    {
        return view('service-order::livewire.service-catalog-management');
    }

    private function loadService(string $id): void
    {
        $service = ServiceCatalogService::findWithStepsOrFail($id);

        $this->mode = 'edit';
        $this->serviceId = $service->id;
        $this->title = 'Editar Servico';
        $this->name = $service->name;
        $this->description = $service->description;
        $this->base_price = (float) $service->base_price;
        $this->is_active = (bool) $service->is_active;
        $this->is_reusable = (bool) $service->is_reusable;

        $this->steps = $service->steps
            ->sortBy('sort_order')
            ->values()
            ->map(function (ServiceCatalogServiceStep $step, int $index): array {
                return [
                    'temp_id' => $step->id,
                    'id' => $step->id,
                    'name' => $step->name,
                    'sort_order' => $index + 1,
                    'is_required' => (bool) $step->is_required,
                    'help_text' => $step->help_text,
                    'technician_report_label' => $step->technician_report_label,
                    'requires_image_proof' => (bool) $step->requires_image_proof,
                    'is_active' => (bool) $step->is_active,
                ];
            })
            ->all();

        if (empty($this->steps)) {
            $this->steps[] = $this->makeEmptyStep(1);
        }
    }

    private function bootNewForm(): void
    {
        $this->mode = 'create';
        $this->serviceId = null;
        $this->title = 'Cadastro de Servico';
        $this->name = '';
        $this->description = null;
        $this->base_price = 0.0;
        $this->is_active = true;
        $this->is_reusable = true;
        $this->steps = [$this->makeEmptyStep(1)];

        $this->resetValidation();
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_reusable' => ['boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.name' => ['required', 'string', 'max:255'],
            'steps.*.is_required' => ['boolean'],
            'steps.*.help_text' => ['nullable', 'string', 'max:1500'],
            'steps.*.technician_report_label' => ['nullable', 'string', 'max:255'],
            'steps.*.requires_image_proof' => ['boolean'],
            'steps.*.is_active' => ['boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do servico.',
            'base_price.required' => 'Informe o valor base.',
            'steps.required' => 'Adicione pelo menos um procedimento.',
            'steps.min' => 'Adicione pelo menos um procedimento.',
            'steps.*.name.required' => 'Informe o nome do procedimento.',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizedStepsForPersistence(): array
    {
        return collect($this->steps)
            ->values()
            ->map(function (array $step, int $index): array {
                return [
                    'name' => trim((string) Arr::get($step, 'name')),
                    'sort_order' => $index + 1,
                    'is_required' => (bool) Arr::get($step, 'is_required', false),
                    'help_text' => $this->normalizeNullableText(Arr::get($step, 'help_text')),
                    'technician_report_label' => $this->normalizeTechnicianReportLabel(Arr::get($step, 'technician_report_label')),
                    'requires_image_proof' => (bool) Arr::get($step, 'requires_image_proof', false),
                    'is_active' => (bool) Arr::get($step, 'is_active', true),
                ];
            })
            ->all();
    }

    private function canAppendStep(): bool
    {
        if (empty($this->steps)) {
            return true;
        }

        $lastStep = $this->steps[array_key_last($this->steps)];
        return trim((string) Arr::get($lastStep, 'name', '')) !== '';
    }

    private function reindexSteps(): void
    {
        $this->steps = collect($this->steps)
            ->values()
            ->map(function (array $step, int $index): array {
                return [
                    'temp_id' => (string) Arr::get($step, 'temp_id', Str::uuid()),
                    'id' => Arr::get($step, 'id'),
                    'name' => (string) Arr::get($step, 'name', ''),
                    'sort_order' => $index + 1,
                    'is_required' => (bool) Arr::get($step, 'is_required', false),
                    'help_text' => (string) Arr::get($step, 'help_text', ''),
                    'technician_report_label' => (string) Arr::get($step, 'technician_report_label', 'Relato tecnico'),
                    'requires_image_proof' => (bool) Arr::get($step, 'requires_image_proof', false),
                    'is_active' => (bool) Arr::get($step, 'is_active', true),
                ];
            })
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function makeEmptyStep(int $sortOrder): array
    {
        return [
            'temp_id' => (string) Str::uuid(),
            'id' => null,
            'name' => '',
            'sort_order' => $sortOrder,
            'is_required' => false,
            'help_text' => '',
            'technician_report_label' => 'Relato tecnico',
            'requires_image_proof' => false,
            'is_active' => true,
        ];
    }

    private function normalizeTechnicianReportLabel(mixed $value): string
    {
        $label = trim((string) ($value ?? ''));

        return $label !== '' ? $label : 'Relato tecnico';
    }

    private function normalizeNullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
