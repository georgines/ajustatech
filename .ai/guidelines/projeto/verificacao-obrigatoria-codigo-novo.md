# Verificacao Obrigatoria de Codigo Novo

## Gate unico de entrega
Toda implementacao nova ou alteracao relevante deve passar por:
1. arquitetura,
2. validacao/sanitizacao,
3. seguranca,
4. performance de requests e queries,
5. testes,
6. restauracao do ambiente.

## Checklist objetivo
1. Codigo ficou no modulo e camada corretos?
2. Inputs foram sanitizados e validados?
3. Mensagens de validacao estao em pt-BR?
4. Superficie sensivel (Livewire/rotas/persistencia) esta protegida?
5. Queries consideram relacionamentos e evitam N+1?
6. Operacoes de volume usam lote (insert/upsert/delete em massa)?
7. Testes cobrem valido/invalido e riscos da mudanca?
8. A implementacao segue padroes do Laravel 11 adotado no projeto?
9. `php artisan dev:reinstall` foi executado apos testes?

## Criterio de pronto
Se algum item falhar, a entrega nao esta pronta.
