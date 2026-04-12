# Servicos e Funcionalidades Modulares

## Objetivo
Padronizar modulos com varias funcionalidades (submodulos) mantendo baixa complexidade.

## Estrutura por funcionalidade
Separar por pasta da feature dentro dos blocos padrao do modulo:
- `Livewire/<Funcionalidade>`
- `Services/<Funcionalidade>`
- `Providers/<Funcionalidade>`
- `Database/{Migrations,Factories,Seeders,Models}/<Funcionalidade>` quando aplicavel
- `Tests/Feature/.../<Funcionalidade>`

## Services e contratos
- Todo service novo deve ter interface.
- Bind interface -> implementacao no provider do modulo/funcionalidade.
- Nao injetar classe concreta direto quando houver contrato.

## Consultas e persistencia
- Consultas de dominio ficam no Model.
- Service orquestra fluxo e transacao, sem centralizar regra de consulta duplicada.
- Em volume, usar lote para insert/update/delete.

## Uploads
- Armazenar por caminho de modulo/funcionalidade.
- Limpar temporarios apos persistencia.

## Checklist rapido
1. Separacao por funcionalidade aplicada?
2. Interface + bind de service implementados?
3. Consultas no Model e orquestracao no Service?
4. Operacoes em lote usadas quando houver volume?
5. Temporarios de upload limpos?
