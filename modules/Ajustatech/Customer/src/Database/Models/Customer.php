<?php

namespace Ajustatech\Customer\Database\Models;

use Ajustatech\Customer\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'person',
        'cpf_cnpj',
        'state_registration',
        'rg',
        'issue_date',
        'issuer',
        'cellphone',
        'phone',
        'email',
        'date_of_birth',
        'marital_status',
        'zip_code',
        'address',
        'number',
        'neighborhood',
        'city',
        'state',
        'birthplace',
        'credit_limit',
        'complement',
        'fathers_name',
        'mothers_name',
        'observations',
        'status',
    ];

    public static function serviceOrderColumns(): array
    {
        return [
            'id',
            'name',
            'person',
            'cpf_cnpj',
            'cellphone',
            'phone',
            'email',
            'zip_code',
            'address',
            'number',
            'neighborhood',
            'city',
            'state',
            'status',
        ];
    }

    public static function search($search, $status = false, $limit = null): mixed
    {
        $querry = self::searchWithoutStatus($search);

        if ($status) {
            $querry->where('status', '1');
        }

        if ($limit) {
            $querry->take($limit);
        }

        return $querry->get();
    }

    public static function searchAll($status = false, $limit = null): mixed
    {
        $querry = self::query();

        if ($status) {
            $querry->where('status', '1');
        }

        if ($limit) {
            $querry->take($limit);
        }

        return $querry->get(['*']);
    }

    public static function searchForServiceOrder(string $search = '', int $limit = 15): Collection
    {
        $term = trim($search);
        $safeLimit = min(max($limit, 1), 50);

        return static::query()
            ->select(static::serviceOrderColumns())
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $builder) use ($term): void {
                    $builder->where('name', 'like', "%{$term}%")
                        ->orWhere('cpf_cnpj', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('cellphone', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($safeLimit)
            ->get();
    }

    public static function findForServiceOrder(string $id): ?self
    {
        return static::query()
            ->select(static::serviceOrderColumns())
            ->find($id);
    }

    public static function findForServiceOrderOrFail(string $id): self
    {
        return static::query()
            ->select(static::serviceOrderColumns())
            ->findOrFail($id);
    }

    public static function createForServiceOrder(array $data): self
    {
        return static::query()->create($data);
    }

    public function toServiceOrderSummary(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => (string) $this->name,
            'person' => (string) $this->person,
            'cpf_cnpj' => (string) $this->cpf_cnpj,
            'document_masked' => $this->maskedDocumentForServiceOrder(),
            'cellphone' => (string) ($this->cellphone ?? ''),
            'phone' => (string) ($this->phone ?? ''),
            'email' => (string) ($this->email ?? ''),
            'zip_code' => (string) ($this->zip_code ?? ''),
            'address' => (string) ($this->address ?? ''),
            'number' => (string) ($this->number ?? ''),
            'neighborhood' => (string) ($this->neighborhood ?? ''),
            'city' => (string) ($this->city ?? ''),
            'state' => (string) ($this->state ?? ''),
            'status' => (string) ($this->status ?? ''),
        ];
    }

    public function toServiceOrderSelection(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => (string) $this->name,
            'cpf_cnpj' => (string) $this->cpf_cnpj,
            'document_masked' => $this->maskedDocumentForServiceOrder(),
        ];
    }

    public function toServiceOrderSnapshot(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => (string) $this->name,
            'person' => (string) $this->person,
            'cpf_cnpj' => (string) $this->cpf_cnpj,
            'cellphone' => (string) ($this->cellphone ?? ''),
            'phone' => (string) ($this->phone ?? ''),
            'email' => (string) ($this->email ?? ''),
            'zip_code' => (string) ($this->zip_code ?? ''),
            'address' => (string) ($this->address ?? ''),
            'number' => (string) ($this->number ?? ''),
            'neighborhood' => (string) ($this->neighborhood ?? ''),
            'city' => (string) ($this->city ?? ''),
            'state' => (string) ($this->state ?? ''),
        ];
    }

    public function maskedDocumentForServiceOrder(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->cpf_cnpj);

        if (strlen($digits) === 11) {
            return substr($digits, 0, 3) . '.***.***-' . substr($digits, 9, 2);
        }

        if (strlen($digits) === 14) {
            return substr($digits, 0, 2) . '.***.***/' . substr($digits, 8, 4) . '-' . substr($digits, 12, 2);
        }

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 2) . str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -2);
    }

    private static function searchWithoutStatus($search): mixed
    {
        return self::where(function ($query) use ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('cpf_cnpj', 'LIKE', '%' . $search . '%')
                ->orWhere('cellphone', 'LIKE', '%' . $search . '%')
                ->orWhere('email', 'LIKE', '%' . $search . '%');
        });
    }

    protected static function newFactory()
    {
        return CustomerFactory::new();
    }
}
