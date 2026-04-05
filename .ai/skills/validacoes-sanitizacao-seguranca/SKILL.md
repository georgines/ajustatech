# Skill: Validacoes, Sanitizacao e Seguranca

## Objetivo
Garantir que toda feature nova tenha entrada de dados protegida e consistente.

## Quando usar
- Ao criar formularios e fluxos de cadastro/edicao.
- Ao receber qualquer entrada vinda de usuario.
- Ao revisar qualidade de dados e seguranca de uma feature.

## Fonte de verdade
- `/.ai/guidelines/projeto/validacoes-sanitizacao-seguranca.md`

## Procedimento obrigatorio
1. Mapear todos os campos de entrada.
2. Definir sanitizacao backend por campo.
3. Definir validacao backend por campo.
4. Definir validacoes/restricoes de frontend (HTML/JS) quando aplicavel.
5. Implementar regras condicionais da regra de negocio.
6. Criar/ajustar testes para casos validos e invalidos.

## Criterios de aceite
- Nenhum campo relevante sem validacao backend.
- Sanitizacao aplicada antes de salvar.
- Frontend com restricoes basicas de entrada.
- Sem persistencia de entrada insegura.

## Nao fazer
- Nao confiar somente em validacao frontend.
- Nao salvar input cru sem sanitizacao.
- Nao deixar regra condicional apenas na interface.
