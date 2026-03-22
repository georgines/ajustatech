<?php

namespace Ajustatech\ServiceOrder\Services;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAttachment;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderFieldValue;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderServiceItem;
use Ajustatech\ServiceOrder\Support\DocumentTemplateRenderer;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ServiceOrderService
{
    public function __construct(
        private readonly EquipmentTypeService $equipmentTypeService,
        private readonly DocumentTemplateRenderer $documentTemplateRenderer
    ) {
    }

    public function create(array $payload): ServiceOrder
    {
        $validated = Validator::make($payload, [
            'equipment_type_id' => ['required', 'string', 'uuid', 'exists:equipment_types,id'],
            'customer_id' => ['nullable', 'string', 'uuid', 'exists:customers,id'],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'equipment_name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'entry_date' => ['nullable', 'date'],
            'reported_issue' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:100'],
        ])->validate();

        $fieldSnapshots = $this->equipmentTypeService->buildActiveFieldSnapshots(Arr::get($validated, 'equipment_type_id'));
        $equipmentTypeModel = EquipmentType::query()
            ->findOrFail(Arr::get($validated, 'equipment_type_id'));

        $equipmentTypeSnapshot = [
            'id' => $equipmentTypeModel->id,
            'name' => $equipmentTypeModel->name,
            'description' => $equipmentTypeModel->description,
        ];

        $resolvedCustomerName = Arr::get($validated, 'customer_name');
        $resolvedCustomerId = Arr::get($validated, 'customer_id');
        if ($resolvedCustomerId) {
            $customer = Customer::query()->findOrFail($resolvedCustomerId);
            $resolvedCustomerName = $customer->name;
        }

        return ServiceOrder::query()->create([
            'equipment_type_id' => Arr::get($validated, 'equipment_type_id'),
            'customer_id' => $resolvedCustomerId,
            'customer_name' => $resolvedCustomerName,
            'equipment_name' => Arr::get($validated, 'equipment_name'),
            'brand' => Arr::get($validated, 'brand'),
            'model' => Arr::get($validated, 'model'),
            'serial_number' => Arr::get($validated, 'serial_number'),
            'entry_date' => Arr::get($validated, 'entry_date', Carbon::now()->toDateString()),
            'reported_issue' => Arr::get($validated, 'reported_issue'),
            'status' => Arr::get($validated, 'status', 'open'),
            'equipment_type_snapshot' => $equipmentTypeSnapshot,
            'fields_snapshot' => $fieldSnapshots,
        ])->fresh(['fieldValues', 'attachments', 'serviceItems']);
    }

    public function syncServices(ServiceOrder|string $order, array $items): ServiceOrder
    {
        $order = $this->resolveOrder($order);
        $catalogServices = ServiceCatalogService::query()
            ->whereIn('id', collect($items)->pluck('service_catalog_service_id')->filter()->all())
            ->with(['steps' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($order, $items, $catalogServices) {
            $order->serviceItems()->delete();

            foreach ($items as $item) {
                $validated = Validator::make($item, [
                    'service_catalog_service_id' => ['required', 'string', 'uuid'],
                    'quantity' => ['required', 'integer', 'min:1', 'max:100'],
                    'discount' => ['nullable', 'numeric', 'min:0'],
                ])->validate();

                /** @var ServiceCatalogService|null $catalogService */
                $catalogService = $catalogServices->get(Arr::get($validated, 'service_catalog_service_id'));
                if (!$catalogService || !$catalogService->is_active) {
                    throw ValidationException::withMessages([
                        'services' => 'Service must exist and be active to be linked to an order.',
                    ]);
                }

                $quantity = (int) Arr::get($validated, 'quantity', 1);
                $unitPrice = (float) $catalogService->base_price;
                $discount = (float) Arr::get($validated, 'discount', 0);
                $lineGross = $quantity * $unitPrice;
                if ($discount > $lineGross) {
                    throw ValidationException::withMessages([
                        'services' => 'Discount cannot be greater than service total.',
                    ]);
                }

                ServiceOrderServiceItem::query()->create([
                    'service_order_id' => $order->id,
                    'service_catalog_service_id' => $catalogService->id,
                    'service_name' => $catalogService->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discount,
                    'service_snapshot' => [
                        'id' => $catalogService->id,
                        'name' => $catalogService->name,
                        'description' => $catalogService->description,
                        'base_price' => $unitPrice,
                        'discount' => $discount,
                        'steps' => $catalogService->steps->map(fn ($step) => [
                            'id' => $step->id,
                            'name' => $step->name,
                            'sort_order' => $step->sort_order,
                            'is_required' => $step->is_required,
                            'help_text' => $step->help_text,
                            'technician_report_label' => $step->technician_report_label,
                            'requires_image_proof' => $step->requires_image_proof,
                        ])->values()->all(),
                    ],
                ]);
            }
        });

        return $order->fresh(['serviceItems']);
    }

    public function getDynamicFields(ServiceOrder|string $order): array
    {
        $order = $this->resolveOrder($order);

        return collect($order->fields_snapshot)
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    public function fillFields(ServiceOrder|string $order, array $values, array $attachments = []): ServiceOrder
    {
        $order = $this->resolveOrder($order);
        $fieldSnapshots = collect($order->fields_snapshot)->keyBy('slug');

        $this->validateRequiredFields($order, $fieldSnapshots, $values, $attachments);

        DB::transaction(function () use ($order, $fieldSnapshots, $values, $attachments) {
            $fieldSnapshots->each(function (array $fieldSnapshot) use ($order, $values, $attachments) {
                $slug = Arr::get($fieldSnapshot, 'slug');
                $fieldType = Arr::get($fieldSnapshot, 'field_type');

                if (EquipmentFieldType::isAttachment($fieldType)) {
                    $this->persistAttachments(
                        $order,
                        $fieldSnapshot,
                        Arr::get($attachments, $slug, [])
                    );
                    return;
                }

                if (!array_key_exists($slug, $values)) {
                    return;
                }

                $this->persistFieldValue($order, $fieldSnapshot, Arr::get($values, $slug));
            });
        });

        return $order->fresh(['fieldValues', 'attachments']);
    }

    public function buildPrintPayload(ServiceOrder|string $order): array
    {
        $order = $this->resolveOrder($order);
        $valuesBySlug = $order->fieldValues->keyBy('field_slug');

        $context = $this->buildDocumentContext($order);

        $printableFields = collect($order->fields_snapshot)
            ->filter(function (array $fieldSnapshot) {
                return (bool) Arr::get($fieldSnapshot, 'is_printable', false)
                    && !EquipmentFieldType::isAttachment(Arr::get($fieldSnapshot, 'field_type'));
            })
            ->sortBy('sort_order')
            ->values()
            ->map(function (array $fieldSnapshot) use ($valuesBySlug, $context) {
                $slug = Arr::get($fieldSnapshot, 'slug');
                /** @var ServiceOrderFieldValue|null $value */
                $value = $valuesBySlug->get($slug);
                $fieldType = Arr::get($fieldSnapshot, 'field_type');
                $renderedValue = null;

                if ($fieldType === EquipmentFieldType::DOCUMENT) {
                    $template = $value?->value_text ?: Arr::get($fieldSnapshot, 'configuration.template', '');
                    $renderedValue = $this->documentTemplateRenderer->render($template, $context);
                } elseif (in_array($fieldType, [EquipmentFieldType::SELECT, EquipmentFieldType::RADIO], true)) {
                    $renderedValue = Arr::get($value?->value_json ?? [], 'label', $value?->value_text);
                } else {
                    $renderedValue = $value?->value_text;
                }

                return [
                    'field_id' => Arr::get($fieldSnapshot, 'id'),
                    'slug' => $slug,
                    'name' => Arr::get($fieldSnapshot, 'name'),
                    'field_type' => $fieldType,
                    'value' => $renderedValue,
                ];
            })
            ->all();

        return [
            'service_order_id' => $order->id,
            'equipment_type' => $order->equipment_type_snapshot,
            'metadata' => [
                'customer_name' => $order->customer_name,
                'equipment_name' => $order->equipment_name,
                'brand' => $order->brand,
                'model' => $order->model,
                'serial_number' => $order->serial_number,
                'entry_date' => optional($order->entry_date)->format('Y-m-d'),
                'reported_issue' => $order->reported_issue,
            ],
            'fields' => $printableFields,
        ];
    }

    private function resolveOrder(ServiceOrder|string $order): ServiceOrder
    {
        if ($order instanceof ServiceOrder) {
            return $order->loadMissing('fieldValues', 'attachments', 'serviceItems');
        }

        return ServiceOrder::query()->with(['fieldValues', 'attachments', 'serviceItems'])->findOrFail($order);
    }

    private function validateRequiredFields(ServiceOrder $order, Collection $fieldSnapshots, array $values, array $attachments): void
    {
        $missing = [];

        foreach ($fieldSnapshots as $fieldSnapshot) {
            if (!(bool) Arr::get($fieldSnapshot, 'is_required', false)) {
                continue;
            }

            $slug = Arr::get($fieldSnapshot, 'slug');
            $fieldType = Arr::get($fieldSnapshot, 'field_type');

            if (EquipmentFieldType::isAttachment($fieldType)) {
                $provided = Arr::get($attachments, $slug, []);
                $existingCount = $order->attachments()->where('field_slug', $slug)->count();
                if (empty($provided) && $existingCount === 0) {
                    $missing[] = $slug;
                }
                continue;
            }

            $value = Arr::get($values, $slug);
            if ($value === null || $value === '') {
                $missing[] = $slug;
            }
        }

        if (!empty($missing)) {
            throw ValidationException::withMessages([
                'values' => 'Required fields are missing: ' . implode(', ', $missing),
            ]);
        }
    }

    private function persistFieldValue(ServiceOrder $order, array $fieldSnapshot, mixed $value): void
    {
        $slug = Arr::get($fieldSnapshot, 'slug');
        $fieldType = Arr::get($fieldSnapshot, 'field_type');
        $configuration = Arr::get($fieldSnapshot, 'configuration', []);
        $normalizedText = is_scalar($value) ? trim((string) $value) : null;
        $valueJson = null;

        if ($fieldType === EquipmentFieldType::TEXT) {
            $maxLength = Arr::get($configuration, 'max_length');
            if ($maxLength && mb_strlen((string) $normalizedText) > (int) $maxLength) {
                throw ValidationException::withMessages([
                    'values.' . $slug => 'The value exceeds max length for field "' . $slug . '".',
                ]);
            }
        }

        if (in_array($fieldType, [EquipmentFieldType::SELECT, EquipmentFieldType::RADIO], true)) {
            $selectedValue = (string) $value;
            $option = collect(Arr::get($fieldSnapshot, 'options', []))
                ->first(fn (array $item) => Arr::get($item, 'value') === $selectedValue);

            if (!$option) {
                throw ValidationException::withMessages([
                    'values.' . $slug => 'Invalid selected option for field "' . $slug . '".',
                ]);
            }

            $normalizedText = $selectedValue;
            $valueJson = [
                'value' => Arr::get($option, 'value'),
                'label' => Arr::get($option, 'label'),
            ];
        }

        if ($fieldType === EquipmentFieldType::DOCUMENT) {
            $normalizedText = (string) $value;
        }

        ServiceOrderFieldValue::query()->updateOrCreate(
            [
                'service_order_id' => $order->id,
                'field_slug' => $slug,
            ],
            [
                'equipment_type_field_id' => Arr::get($fieldSnapshot, 'id'),
                'field_type' => $fieldType,
                'value_text' => $normalizedText,
                'value_json' => $valueJson,
                'field_snapshot' => Arr::only($fieldSnapshot, ['id', 'name', 'slug', 'field_type', 'sort_order', 'is_printable']),
            ]
        );
    }

    private function persistAttachments(ServiceOrder $order, array $fieldSnapshot, array $attachments): void
    {
        if (empty($attachments)) {
            return;
        }

        $slug = Arr::get($fieldSnapshot, 'slug');
        $fieldType = Arr::get($fieldSnapshot, 'field_type');
        $configuration = Arr::get($fieldSnapshot, 'configuration', []);
        $allowedExtensions = collect(Arr::get($configuration, 'allowed_extensions', []))
            ->map(fn (string $extension) => strtolower($extension))
            ->all();

        if (empty($allowedExtensions)) {
            throw ValidationException::withMessages([
                'attachments.' . $slug => 'Allowed extensions must be configured for attachment fields.',
            ]);
        }

        $existingCount = $order->attachments()->where('field_slug', $slug)->count();
        $maxFiles = Arr::get($configuration, 'max_files');
        if ($fieldType === EquipmentFieldType::PHOTO && $maxFiles !== null && ($existingCount + count($attachments)) > (int) $maxFiles) {
            throw ValidationException::withMessages([
                'attachments.' . $slug => 'Photo field "' . $slug . '" exceeded the max files limit.',
            ]);
        }

        foreach ($attachments as $attachment) {
            $validatedAttachment = Validator::make($attachment, [
                'disk' => ['nullable', 'string', 'max:50'],
                'path' => ['required', 'string', 'max:500'],
                'original_name' => ['required', 'string', 'max:255'],
                'mime_type' => ['nullable', 'string', 'max:255'],
                'extension' => ['required', 'string', 'max:10'],
                'size' => ['nullable', 'integer', 'min:0'],
                'metadata' => ['nullable', 'array'],
            ])->validate();

            if (!in_array(strtolower(Arr::get($validatedAttachment, 'extension')), $allowedExtensions, true)) {
                throw ValidationException::withMessages([
                    'attachments.' . $slug => 'Attachment extension is not allowed for field "' . $slug . '".',
                ]);
            }

            ServiceOrderAttachment::query()->create([
                'service_order_id' => $order->id,
                'equipment_type_field_id' => Arr::get($fieldSnapshot, 'id'),
                'field_slug' => $slug,
                'attachment_type' => $fieldType,
                'disk' => Arr::get($validatedAttachment, 'disk', 'public'),
                'path' => Arr::get($validatedAttachment, 'path'),
                'original_name' => Arr::get($validatedAttachment, 'original_name'),
                'mime_type' => Arr::get($validatedAttachment, 'mime_type'),
                'extension' => Arr::get($validatedAttachment, 'extension'),
                'size' => Arr::get($validatedAttachment, 'size'),
                'metadata' => Arr::get($validatedAttachment, 'metadata', []),
            ]);
        }
    }

    private function buildDocumentContext(ServiceOrder $order): array
    {
        return [
            'cliente_nome' => $order->customer_name,
            'equipamento_nome' => $order->equipment_name,
            'marca' => $order->brand,
            'modelo' => $order->model,
            'numero_serie' => $order->serial_number,
            'data_entrada' => optional($order->entry_date)->format('Y-m-d'),
            'defeito_relatado' => $order->reported_issue,
        ];
    }
}
