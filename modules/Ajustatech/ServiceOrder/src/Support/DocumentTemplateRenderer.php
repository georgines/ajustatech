<?php

namespace Ajustatech\ServiceOrder\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class DocumentTemplateRenderer
{
    public function render(string $template, array $context): string
    {
        $invalidVariables = DocumentVariableCatalog::invalidVariables($template);

        if (!empty($invalidVariables)) {
            throw new InvalidArgumentException('Invalid document variables: ' . implode(', ', $invalidVariables));
        }

        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function (array $matches) use ($context) {
            $key = Arr::get($matches, 1, '');
            $value = Arr::get($context, $key, '');

            if ($value instanceof Carbon) {
                return $value->format('Y-m-d');
            }

            return is_scalar($value) ? (string) $value : '';
        }, $template) ?? $template;
    }
}

