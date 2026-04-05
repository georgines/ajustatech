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
    public bool $hasHelp = false;
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
        $this->hasHelp = $this->hasHelpContent(
            $this->helpText,
            $this->helpImageUrl,
            $this->helpVideoUrl
        );
        $this->title = trans('service-order::messages.procedure_edit_title');
    }

    public function save(ProcedureServiceInterface $service)
    {
        $this->sanitizeInputs();

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'value' => 'required|numeric|min:0|max:999999.99',
            'hasHelp' => 'required|boolean',
            'helpText' => 'nullable|string|max:2000',
            'helpImageUrl' => 'nullable|url|max:1000',
            'helpVideoUrl' => 'nullable|url|max:1000',
        ], [], $this->validationAttributes());

        if ($this->hasHelp && !$this->hasHelpContent($this->helpText, $this->helpImageUrl, $this->helpVideoUrl)) {
            $this->addError('hasHelp', trans('service-order::messages.procedure_help_required'));
            return;
        }

        $payload = [
            'name' => $this->name,
            'description' => $this->nullableValue($this->description),
            'value' => $this->value,
            'help_text' => $this->hasHelp ? $this->nullableValue($this->helpText) : null,
            'help_image_url' => $this->hasHelp ? $this->nullableValue($this->helpImageUrl) : null,
            'help_video_url' => $this->hasHelp ? $this->nullableValue($this->helpVideoUrl) : null,
        ];

        if ($this->mode === 'edit' && $this->procedureId) {
            $service->updateProcedure($this->procedureId, $payload);
        } else {
            $service->createProcedure($payload);
        }

        return redirect()->route('service-order-procedures-show');
    }

    public function updatedHasHelp(bool $value): void
    {
        if ($value) {
            return;
        }

        $this->helpText = '';
        $this->helpImageUrl = '';
        $this->helpVideoUrl = '';
        $this->resetValidation(['hasHelp', 'helpText', 'helpImageUrl', 'helpVideoUrl']);
    }

    private function sanitizeInputs(): void
    {
        $this->name = $this->sanitizeText($this->name, 255);
        $this->description = $this->sanitizeText($this->description, 1000);
        $this->helpText = $this->sanitizeText($this->helpText, 2000);
        $this->helpImageUrl = $this->sanitizeUrl($this->helpImageUrl);
        $this->helpVideoUrl = $this->sanitizeUrl($this->helpVideoUrl);

        if ($this->value !== null && $this->value !== '') {
            $this->value = round((float) $this->value, 2);
        }
    }

    private function sanitizeText(?string $value, int $limit): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $value));
        $withoutTags = strip_tags((string) $normalized);

        return mb_substr($withoutTags, 0, $limit);
    }

    private function sanitizeUrl(?string $value): string
    {
        $normalized = trim((string) $value);
        $withoutTags = strip_tags($normalized);

        return mb_substr($withoutTags, 0, 1000);
    }

    private function hasHelpContent(?string $text, ?string $imageUrl, ?string $videoUrl): bool
    {
        return $this->nullableValue($text) !== null
            || $this->nullableValue($imageUrl) !== null
            || $this->nullableValue($videoUrl) !== null;
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
            'hasHelp' => trans('service-order::messages.procedure_help_switch'),
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
