# Vuexy UI Integration Blueprint

## 1) Equipment Type Listing
- Use Vuexy DataTable inside a card.
- Mandatory columns: `name`, `status`, `field_count`, `actions`.
- Top action button: `Novo tipo de equipamento`.
- Row actions: `edit`, `activate/deactivate`, `view`.
- Data source suggestion: `EquipmentTypeListViewModel::fromCollection(...)`.

## 2) Equipment Type Editor
- Vuexy main card with tabs/sections:
- Section `general_data`: name, description, active status.
- Section `dynamic_fields`: sortable repeater.
- Each row must expose switches:
- `is_required`
- `is_printable`
- `is_active`
- Field type components:
- `photo`: card with individual preview.
- `text`: input/textarea with help and placeholder.
- `select`: Vuexy select with configurable options.
- `radio`: Vuexy radio group with configurable options.
- `file`: upload input with preview in modal/new tab.
- `document`: large textarea with available variable list.
- Data source suggestion: `EquipmentTypeEditorViewModel::fromModel(...)`.

## 3) Service Order Opening Screen
- On equipment type select, dynamically mount ordered active fields from snapshot.
- Show required visual feedback.
- Differentiate printable and internal fields visually.
- Never include `photo` and `file` in printable payload.
- Data source suggestion: `ServiceOrderDynamicFormViewModel::fromOrder(...)`.

## 4) Printing Contract
- Consume payload from `ServiceOrderService::buildPrintPayload(...)`.
- Render only `fields` returned by payload.
- Document fields already support variable rendering using:
- `{{cliente_nome}}`
- `{{equipamento_nome}}`
- `{{marca}}`
- `{{modelo}}`
- `{{numero_serie}}`
- `{{data_entrada}}`
- `{{defeito_relatado}}`
