# ServiceOrder Module Architecture

## Domain Separation
- Configuration layer:
- `equipment_types`
- `equipment_type_fields`
- `equipment_type_field_options`
- Execution layer:
- `service_orders`
- `service_order_field_values`
- `service_order_attachments`

## Core Services
- `EquipmentTypeService`: validates and persists equipment type templates and dynamic fields.
- `ServiceOrderService`: opens service orders from template snapshot, validates fills, persists values/attachments, builds print payload.
- `EquipmentFieldConfigurationValidator`: field-level business validation by field type.
- `DocumentTemplateRenderer`: document variable rendering.

## Snapshot Strategy
- `service_orders.equipment_type_snapshot`: minimal immutable type metadata at open time.
- `service_orders.fields_snapshot`: immutable ordered dynamic field definitions used by the order.
- `service_order_field_values.field_snapshot`: field metadata at fill time.

## Frontend Contracts
- `EquipmentTypeListViewModel`
- `EquipmentTypeEditorViewModel`
- `ServiceOrderDynamicFormViewModel`

These contracts are prepared to feed Livewire components and Vuexy cards/tables/forms.
