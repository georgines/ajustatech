<?php

namespace Ajustatech\Settings\Database\Models\ServiceOrder;

use Ajustatech\Settings\Database\Factories\ServiceOrder\ServiceOrderStatusFlowFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderStatusFlow extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_status_flows';

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_terminal',
        'is_default_initial',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_terminal' => 'boolean',
        'is_default_initial' => 'boolean',
    ];

    protected static function newFactory()
    {
        return ServiceOrderStatusFlowFactory::new();
    }

    public static function listForSettings(): Collection
    {
        $codes = collect(static::defaultRows())->pluck('code')->all();

        return static::query()
            ->whereIn('code', $codes)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'sort_order', 'is_terminal', 'is_default_initial']);
    }

    public static function defaultInitialId(): ?string
    {
        static::ensureDefaultRows();

        return static::query()
            ->where('code', 'entrada')
            ->value('id');
    }

    public static function ensureDefaultRows(): void
    {
        $rows = static::defaultRows();

        static::query()->upsert(
            $rows,
            ['code'],
            ['name', 'sort_order', 'is_terminal', 'is_default_initial', 'updated_at']
        );
    }

    public static function defaultRows(): array
    {
        $now = now();

        return [
            [
                'code' => 'entrada',
                'name' => 'Entrada',
                'sort_order' => 1,
                'is_terminal' => false,
                'is_default_initial' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'em_analise',
                'name' => 'Em analise',
                'sort_order' => 2,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'aguardando_orcamento',
                'name' => 'Aguardando orcamento',
                'sort_order' => 3,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'aguardando_aprovacao',
                'name' => 'Aguardando aprovacao',
                'sort_order' => 4,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'aguardando_peca',
                'name' => 'Aguardando peca',
                'sort_order' => 5,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'executando',
                'name' => 'Executando',
                'sort_order' => 6,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'orcamento_reprovado',
                'name' => 'Orcamento reprovado',
                'sort_order' => 7,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'montagem',
                'name' => 'Montagem',
                'sort_order' => 8,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'entrega',
                'name' => 'Entrega',
                'sort_order' => 9,
                'is_terminal' => false,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'concluido',
                'name' => 'Concluido',
                'sort_order' => 10,
                'is_terminal' => true,
                'is_default_initial' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }
}


