<?php

namespace Ajustatech\ServiceOrder\Livewire\Procedure;

use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ProcedureManagement extends Component
{
    public $title;
    public $mode = 'create';
    public $procedureId = null;

    public $name = '';
    public $description = '';
    public $value = null;
    public $helpText = '';
    public $helpImageUrl = '';
    public $helpVideoUrl = '';

    public function mount(ProcedureServiceInterface $service, ?string $id = null): void
    {
        $this->title = trans('service-order::messages.procedure_create_title');

        if (!$id) {
            return;
        }

        $procedure = $service->findProcedure($id);
        $this->mode = 'edit';
        $this->procedureId = $procedure->id;
        $this->name = $procedure->name;
        $this->description = (string) $procedure->description;
        $this->value = (float) $procedure->value;
        $this->helpText = (string) $procedure->help_text;
        $this->helpImageUrl = (string) $procedure->help_image_url;
        $this->helpVideoUrl = (string) $procedure->help_video_url;
        $this->title = trans('service-order::messages.procedure_edit_title');
    }

    public function save(ProcedureServiceInterface $service)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|numeric|min:0',
            'helpText' => 'nullable|string',
            'helpImageUrl' => 'nullable|url|max:1000',
            'helpVideoUrl' => 'nullable|url|max:1000',
        ], [], $this->validationAttributes());

        $payload = [
            'name' => $this->name,
            'description' => $this->nullableValue($this->description),
            'value' => $this->value,
            'help_text' => $this->nullableValue($this->helpText),
            'help_image_url' => $this->nullableValue($this->helpImageUrl),
            'help_video_url' => $this->nullableValue($this->helpVideoUrl),
        ];

        if ($this->mode === 'edit' && $this->procedureId) {
            $service->updateProcedure($this->procedureId, $payload);
        } else {
            $service->createProcedure($payload);
        }

        return redirect()->route('service-order-procedures-show');
    }

    private function nullableValue(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function validationAttributes(): array
    {
        return [
            'name' => trans('service-order::messages.procedure_name'),
            'description' => trans('service-order::messages.procedure_description'),
            'value' => trans('service-order::messages.procedure_value'),
            'helpText' => trans('service-order::messages.procedure_help_text'),
            'helpImageUrl' => trans('service-order::messages.procedure_help_image'),
            'helpVideoUrl' => trans('service-order::messages.procedure_help_video'),
        ];
    }

    public function render()
    {
        return view('service-order::livewire.procedure.procedure-management');
    }
}
