# ServiceOrder Module Architecture

## Domain Separation
- Configuration layer:
- `equipment_types`
- `equipment_type_fields`
- `equipment_type_field_options`
- `analysis_types`
- `analysis_sections`
- `analysis_questions`
- `analysis_question_options`
- `analysis_question_complementary_fields`
- `analysis_conditional_rules`
- `analysis_consequences`
- `analysis_technical_actions`
- `analysis_technical_action_prices`
- Execution layer:
- `service_orders`
- `service_order_field_values`
- `service_order_attachments`
- `service_order_analysis_services`
- `service_order_analysis_sections`
- `service_order_analysis_questions`
- `service_order_analysis_question_options`
- `service_order_analysis_complementary_fields`
- `service_order_analysis_responses`
- `service_order_analysis_complementary_responses`
- `service_order_analysis_attachments`
- `service_order_technical_findings`

## Core Services
- `EquipmentTypeService`: validates and persists equipment type templates and dynamic fields.
- `ServiceOrderService`: opens service orders from template snapshot, validates fills, persists values/attachments, builds print payload.
- `AnalysisExecutionService`: creates immutable analysis instances per order, stores responses/evidence, applies consequences and generates technical findings.
- `EquipmentFieldConfigurationValidator`: field-level business validation by field type.
- `DocumentTemplateRenderer`: document variable rendering.

## Snapshot Strategy
- `service_orders.equipment_type_snapshot`: minimal immutable type metadata at open time.
- `service_orders.fields_snapshot`: immutable ordered dynamic field definitions used by the order.
- `service_order_field_values.field_snapshot`: field metadata at fill time.
- `service_order_analysis_services.analysis_type_snapshot`: immutable analysis type metadata at analysis start.
- `service_order_analysis_*`: immutable instantiated structure for sections/questions/options/complementary fields.

## Frontend Contracts
- `EquipmentTypeListViewModel`
- `EquipmentTypeEditorViewModel`
- `ServiceOrderDynamicFormViewModel`

These contracts are prepared to feed Livewire components and Vuexy cards/tables/forms.
