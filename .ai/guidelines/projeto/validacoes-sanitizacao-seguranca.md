# Validacoes, Sanitizacao e Seguranca de Entrada

## Objetivo
Padronizar tratamento de entrada de dados em backend e frontend para reduzir risco de dados invalidos, inconsistentes ou inseguros.

## Regra obrigatoria para toda feature nova
Toda entrada do usuario deve passar por:
1. Sanitizacao.
2. Validacao.
3. Persistencia somente de dados aprovados.

## Backend (obrigatorio)
- Sanitizar antes de validar e salvar:
  - `trim` em strings;
  - normalizacao de espacos;
  - remocao de tags HTML quando o campo nao for rich text;
  - limite de tamanho defensivo.
- Validar com regras explicitas por campo (`required`, `nullable`, `max`, `numeric`, `url`, `in`, etc.).
- Regras condicionais devem refletir regras de negocio (ex.: chave ligada exige pelo menos um campo).
- Nunca confiar em restricao apenas de frontend.

## Frontend (obrigatorio quando houver input)
- Aplicar restricoes HTML minimas:
  - `maxlength`, `min`, `max`, `step`, `inputmode`, `type` adequado.
- Em JS puro (sem Livewire para aquele input), sanitizar no cliente antes de enviar.
- Mensagens de erro devem ser claras e alinhadas ao campo.
- Toda mensagem de validacao deve ser renderizada imediatamente abaixo do campo correspondente (evitar bloco de erros global no topo/rodape do formulario).

## Seguranca
- Evitar persistir HTML bruto quando nao necessario.
- Validar e restringir URLs a formato valido.
- Evitar interpolacao insegura em scripts/atributos; usar helpers de escape/serializacao.
- Nao executar conteudo de entrada do usuario no cliente.

## Uploads e arquivos temporarios (obrigatorio)
- Todo fluxo de upload deve prever limpeza de temporarios apos salvar os arquivos definitivos.
- Nao manter arquivo temporario sem necessidade depois da persistencia.
- Validar periodicamente se nao ha acumulo de lixo em storage temporario da feature.

## Checklist de conclusao
1. Todo campo recebeu sanitizacao no backend?
2. Todo campo recebeu validacao no backend?
3. Campos de frontend possuem restricoes basicas?
4. Regras condicionais de negocio estao cobertas?
5. Testes cobrem casos validos e invalidos principais?
6. Fluxos de upload limpam os temporarios apos salvar?
