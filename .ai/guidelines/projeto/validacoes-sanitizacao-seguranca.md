# Validacoes, Sanitizacao e Seguranca de Entrada

## Regra obrigatoria
Todo input novo deve passar por:
1. sanitizacao,
2. validacao backend,
3. persistencia apenas do dado validado.

## Backend
- Sanitizar (`trim`, normalizacao, `strip_tags` quando cabivel, limite defensivo).
- Definir regras explicitas por campo.
- Aplicar regras condicionais de negocio no backend.

## Frontend
- Usar restricoes basicas (`maxlength`, `min`, `max`, `step`, `type`, `inputmode`).
- Renderizar erro junto ao campo.

## Idioma das mensagens
- Mensagens de validacao para usuario final: portugues (pt-BR).

## Uploads
- Validar extensao, mime, tamanho e dimensao quando aplicavel.
- Remover arquivo temporario apos salvar definitivo.

## Checklist rapido
1. Todos os campos relevantes foram validados?
2. Sanitizacao aplicada antes de salvar?
3. Mensagens de erro estao em pt-BR?
4. Temporarios de upload sao limpos?
