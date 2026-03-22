<?php

namespace Ajustatech\ServiceOrder\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class DocumentVariableCatalog
{
    public const CLIENT_NAME = 'cliente_nome';
    public const EQUIPMENT_NAME = 'equipamento_nome';
    public const BRAND = 'marca';
    public const MODEL = 'modelo';
    public const SERIAL_NUMBER = 'numero_serie';
    public const ENTRY_DATE = 'data_entrada';
    public const REPORTED_ISSUE = 'defeito_relatado';

    public static function allowedVariables(): array
    {
        return [
            self::CLIENT_NAME,
            self::EQUIPMENT_NAME,
            self::BRAND,
            self::MODEL,
            self::SERIAL_NUMBER,
            self::ENTRY_DATE,
            self::REPORTED_ISSUE,
        ];
    }

    public static function extractVariables(string $template): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $template, $matches);

        return collect(Arr::get($matches, 1, []))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function invalidVariables(string $template): array
    {
        $allowed = self::allowedVariables();

        return collect(self::extractVariables($template))
            ->reject(fn (string $variable) => in_array($variable, $allowed, true))
            ->values()
            ->all();
    }

    public static function toUiList(): Collection
    {
        return collect(self::allowedVariables())->map(fn (string $item) => [
            'name' => $item,
            'token' => '{{' . $item . '}}',
        ]);
    }
}

