<?php

namespace Ajustatech\ServiceOrder\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EquipmentFieldConfigurationValidator
{
    public function validate(array $payload): array
    {
        $validator = Validator::make($payload, [
            'field_type' => ['required', 'string', Rule::in(EquipmentFieldType::values())],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_required' => ['required', 'boolean'],
            'is_printable' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'configuration' => ['nullable', 'array'],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['required_with:options', 'string', 'max:255'],
            'options.*.value' => ['required_with:options', 'string', 'max:255'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:1'],
            'options.*.is_active' => ['nullable', 'boolean'],
        ]);

        $validated = $validator->validate();

        $type = Arr::get($validated, 'field_type');
        $configuration = Arr::get($validated, 'configuration', []);
        $options = Arr::get($validated, 'options', []);
        $isPrintable = (bool) Arr::get($validated, 'is_printable', false);

        if (EquipmentFieldType::isAttachment($type) && $isPrintable) {
            throw ValidationException::withMessages([
                'is_printable' => 'Photo and file fields cannot be printable.',
            ]);
        }

        if (EquipmentFieldType::acceptsOptions($type) && empty($options)) {
            throw ValidationException::withMessages([
                'options' => 'Select and radio fields must define options.',
            ]);
        }

        if (in_array($type, [EquipmentFieldType::PHOTO, EquipmentFieldType::FILE], true)) {
            $this->validateAttachmentConfiguration($configuration, $type);
        }

        if ($type === EquipmentFieldType::TEXT) {
            $this->validateTextConfiguration($configuration);
        }

        if ($type === EquipmentFieldType::DOCUMENT) {
            $this->validateDocumentConfiguration($configuration);
        }

        return $validated;
    }

    private function validateAttachmentConfiguration(array $configuration, string $fieldType): void
    {
        $rules = [
            'allowed_extensions' => ['required', 'array', 'min:1'],
            'allowed_extensions.*' => ['required', 'string', 'max:10'],
        ];

        if ($fieldType === EquipmentFieldType::PHOTO) {
            $rules['max_files'] = ['required', 'integer', 'min:1', 'max:30'];
        }

        if ($fieldType === EquipmentFieldType::FILE) {
            $rules['preview_mode'] = ['nullable', Rule::in(['modal', 'new_tab'])];
        }

        Validator::make($configuration, $rules)->validate();
    }

    private function validateTextConfiguration(array $configuration): void
    {
        Validator::make($configuration, [
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help' => ['nullable', 'string', 'max:1000'],
            'max_length' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ])->validate();
    }

    private function validateDocumentConfiguration(array $configuration): void
    {
        $validated = Validator::make($configuration, [
            'template' => ['nullable', 'string'],
            'help' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $template = Arr::get($validated, 'template', '');
        if ($template === '') {
            return;
        }

        $invalidVariables = DocumentVariableCatalog::invalidVariables($template);
        if (!empty($invalidVariables)) {
            throw ValidationException::withMessages([
                'configuration.template' => 'Invalid document variables: ' . implode(', ', $invalidVariables),
            ]);
        }
    }
}

