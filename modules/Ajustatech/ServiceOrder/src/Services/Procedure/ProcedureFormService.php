<?php

namespace Ajustatech\ServiceOrder\Services\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureFormServiceInterface;

class ProcedureFormService implements ProcedureFormServiceInterface
{
    public function initializeCreateState(ProcedureManagement $component): void
    {
        $component->title = trans('service-order::messages.procedure_create_title');
    }

    public function fillFromProcedure(ProcedureManagement $component, ServiceOrderProcedure $procedure): void
    {
        $component->name = (string) $procedure->name;
        $component->description = (string) ($procedure->description ?? '');
        $component->value = (float) $procedure->value;
        $component->helpText = (string) ($procedure->help_text ?? '');
        $component->title = trans('service-order::messages.procedure_edit_title');
    }

    public function sanitizeInputs(ProcedureManagement $component): void
    {
        $component->name = $this->sanitizeText($component->name, 255);
        $component->description = $this->sanitizeText($component->description, 1000);
        $component->helpText = $this->sanitizeText($component->helpText, 2000);

        if ($component->value !== null && $component->value !== '') {
            $component->value = round((float) $component->value, 2);
        }
    }

    public function buildPayload(ProcedureManagement $component): array
    {
        return [
            'name' => $component->name,
            'description' => $this->nullableValue($component->description),
            'value' => $component->value,
            'help_text' => $component->hasHelp ? $this->nullableValue($component->helpText) : null,
            'help_image_url' => null,
            'help_video_url' => null,
        ];
    }

    public function validationRules(ProcedureManagement $component): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'value' => 'required|numeric|min:0|max:999999.99',
            'hasHelp' => 'required|boolean',
            'helpText' => 'nullable|string|max:2000',
        ];

        if ($component->hasHelp) {
            $rules['videoItems.*.url'] = 'nullable|url|max:1000';
            $rules['videoItems.*.name'] = 'required_with:videoItems.*.url|string|max:255';
            $rules['videoItems.*.description'] = 'nullable|string|max:500';
            $rules['imageItems.*.file'] = 'nullable|image|max:5120';
            $rules['imageItems.*.name'] = 'required_with:imageItems.*.file|string|max:255';
            $rules['imageItems.*.description'] = 'nullable|string|max:500';
            $rules['pdfItems.*.file'] = 'nullable|file|mimes:pdf|max:10240';
            $rules['pdfItems.*.name'] = 'required_with:pdfItems.*.file|string|max:255';
            $rules['pdfItems.*.description'] = 'nullable|string|max:500';
        }

        return $rules;
    }

    public function validationAttributes(): array
    {
        return [
            'name' => trans('service-order::messages.procedure_name'),
            'description' => trans('service-order::messages.procedure_description'),
            'value' => trans('service-order::messages.procedure_value'),
            'hasHelp' => trans('service-order::messages.procedure_help_switch'),
            'helpText' => trans('service-order::messages.procedure_help_text'),
            'videoItems.*.url' => trans('service-order::messages.procedure_help_video'),
            'videoItems.*.name' => trans('service-order::messages.procedure_help_media_name'),
            'videoItems.*.description' => trans('service-order::messages.procedure_help_media_text'),
            'imageItems.*.file' => trans('service-order::messages.procedure_help_image'),
            'imageItems.*.name' => trans('service-order::messages.procedure_help_media_name'),
            'imageItems.*.description' => trans('service-order::messages.procedure_help_media_text'),
            'pdfItems.*.file' => trans('service-order::messages.procedure_help_pdf'),
            'pdfItems.*.name' => trans('service-order::messages.procedure_help_media_name'),
            'pdfItems.*.description' => trans('service-order::messages.procedure_help_media_text'),
            'newMediaType' => trans('service-order::messages.procedure_help_media_type'),
            'newMediaName' => trans('service-order::messages.procedure_help_media_name'),
            'newMediaDescription' => trans('service-order::messages.procedure_help_media_text'),
            'newMediaUrl' => trans('service-order::messages.procedure_help_video'),
            'newMediaFile' => trans('service-order::messages.procedure_help_media_file'),
        ];
    }

    private function sanitizeText(?string $value, int $limit): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $value));
        $withoutTags = strip_tags((string) $normalized);

        return mb_substr($withoutTags, 0, $limit);
    }

    private function nullableValue(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
