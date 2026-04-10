---
name: gate-codigo-novo
description: "Use quando criar ou alterar codigo relevante e precisar aplicar o gate completo de arquitetura, validacao, seguranca, performance, testes e dev:reinstall."
---

# Skill: Gate de Codigo Novo

## Objetivo

Aplicar um gate obrigatorio de verificacao em todo codigo novo ou alteracao relevante antes de considerar a implementacao pronta.

## Quando usar

- Sempre que criar codigo novo.
- Sempre que alterar regra de negocio, persistencia, validacao, Livewire, models, migrations, seeds, services ou UI com input.
- Sempre que houver fluxo novo de salvar, editar, excluir, carregar, listar ou confirmar acao.

## Papel desta skill

Esta skill nao substitui as skills existentes do projeto.
Ela funciona como skill-orquestradora para garantir que toda entrega nova passe pelos checks minimos de:
- arquitetura;
- validacao e sanitizacao;
- seguranca;
- performance de requisicoes e queries;
- testes;
- restauracao do ambiente apos testes.

## Fontes de verdade obrigatorias

- `/.ai/guidelines/projeto/verificacao-obrigatoria-codigo-novo.md`
- `/.ai/guidelines/projeto/padroes.md`
- `/.ai/guidelines/projeto/validacoes-sanitizacao-seguranca.md`
- `/.ai/guidelines/projeto/seguranca.md`
- `/.ai/guidelines/projeto/otimizacao-solicitacoes.md`
- `/.ai/guidelines/projeto/testes-feature-tdd.md`
- `/.ai/guidelines/projeto/pos-testes-dev-reinstall.md`

## Checklist operacional obrigatorio

1. Confirmar onde a implementacao se encaixa na arquitetura modular do projeto.
2. Mapear entradas de dados e aplicar sanitizacao + validacao backend.
3. Verificar riscos de seguranca da superficie alterada.
4. Verificar possibilidade de reduzir requisicoes HTTP/Livewire e queries no banco.
5. Criar ou ajustar testes antes da implementacao quando a mudanca for feature/comportamento.
6. Cobrir cenarios validos, invalidos, seguranca e performance quando aplicavel.
7. Executar a suite relevante.
8. Executar `php artisan dev:reinstall` ao final dos testes.

## Regras minimas por tipo de alteracao

### Se houver input/formulario

- Aplicar `maxlength`, `min`, `max`, `step`, `type` e `inputmode` quando fizer sentido.
- Sanitizar no backend antes de salvar.
- Validar no backend todos os campos relevantes.
- Cobrir casos invalidos principais em testes.

### Se houver Livewire

- Tratar propriedades publicas como entrada nao confiavel.
- Travar com `Locked` o que for estado sensivel ou identificador de recurso.
- Evitar requests ao backend para acoes puramente visuais quando o estado puder ser local.
- Cobrir tampering e fluxo principal com testes.

### Se houver persistencia/model

- Manter consultas no Model e orquestracao no Service/Component.
- Evitar query em loop e N+1.
- Medir/validar quantidade de queries em fluxos criticos quando houver risco de regressao.
- Garantir migration, factory, seeder e testes quando for modelo novo ou regra estrutural nova.

### Se houver seeds/factory

- Usar factory sempre que possivel.
- Preferir lote para volume.
- Garantir dados relacionais corretos.

## Criterio de pronto

Uma implementacao nova so pode ser considerada pronta quando:
1. Passou pelo checklist do gate.
2. Tem validacao e seguranca coerentes com a superficie alterada.
3. Tem testes relevantes cobrindo comportamento, regressao e casos invalidos.
4. Teve o ambiente recomposto apos os testes.

## Nao fazer

- Nao encerrar mudanca nova sem revisar seguranca e validacao.
- Nao depender apenas de guideline isolado sem aplicar o gate completo.
- Nao considerar codigo pronto so porque funciona visualmente.

## Regra obrigatoria de qualidade profissional

- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).