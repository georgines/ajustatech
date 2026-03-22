<?php

namespace Ajustatech\ServiceOrder\Tests\Unit;

use Ajustatech\ServiceOrder\Support\DocumentTemplateRenderer;
use InvalidArgumentException;
use Tests\TestCase;

class DocumentTemplateRendererTest extends TestCase
{
    public function test_renders_document_template_with_context_variables(): void
    {
        $renderer = new DocumentTemplateRenderer();
        $template = 'Cliente {{cliente_nome}} - Equipamento {{equipamento_nome}} - Serie {{numero_serie}}';

        $rendered = $renderer->render($template, [
            'cliente_nome' => 'Carlos',
            'equipamento_nome' => 'Notebook',
            'numero_serie' => 'SN123',
        ]);

        $this->assertSame('Cliente Carlos - Equipamento Notebook - Serie SN123', $rendered);
    }

    public function test_throws_exception_when_document_has_invalid_variable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $renderer = new DocumentTemplateRenderer();
        $renderer->render('Teste {{variavel_invalida}}', []);
    }
}

