<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Illuminate\Support\Arr;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceOrderDocuments extends Component
{
    public string $title = 'Documentos da Ordem';
    public string $orderId;

    public function mount(string $id): void
    {
        $order = ServiceOrder::query()->findOrFail($id);
        $this->orderId = $order->id;
    }

    public function render()
    {
        $order = ServiceOrder::query()->with('fieldValues')->findOrFail($this->orderId);
        $valuesBySlug = $order->fieldValues->keyBy('field_slug');

        $documents = collect($order->fields_snapshot)
            ->filter(fn (array $field) => Arr::get($field, 'field_type') === 'document')
            ->sortBy('sort_order')
            ->values()
            ->map(function (array $field) use ($valuesBySlug) {
                $slug = Arr::get($field, 'slug');
                $value = $valuesBySlug->get($slug);

                return [
                    'name' => Arr::get($field, 'name', $slug),
                    'slug' => $slug,
                    'value' => $value?->value_text,
                ];
            })
            ->all();

        return view('service-order::livewire.show-service-order-documents', [
            'order' => $order,
            'documents' => $documents,
        ]);
    }
}

