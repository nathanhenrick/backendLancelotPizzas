---
name: saas-multitenancy
description: Projeta e verifica o isolamento entre pizzarias (multi-tenancy) na API Laravel do Lancelot Pizzas: escopo de dados por loja, autorização, resolução do tenant e testes de acesso cruzado. Use ao transformar o sistema em SaaS, criar recurso novo que guarda dados de loja, ou revisar vazamento entre lojas.
---

# Multi-tenancy — isolamento entre pizzarias

## Ponto de partida (verifique antes de agir)
Hoje o sistema é de **uma pizzaria só**: `configuracoes` é registro único e tabelas como `sabores`, `bordas`, `bebidas`, `esfirras`, `adicionais`, `pedidos`, `users` não têm `tenant_id`. Rotas públicas (`GET /sabores`, `POST /pedidos` etc.) não sabem de qual loja se fala. Confirme isso lendo `database/migrations`, `app/Models` e `routes/api.php` antes de propor qualquer coisa.

## Quando usar
- Planejar ou implementar multi-tenancy.
- Adicionar tabela/endpoint/job/export que lida com dados de loja.
- Revisar se algo pode vazar entre lojas.

## Procedimento
1. **Inventário de dados**: classifique cada tabela como *por loja* (cardápio, pedidos, configurações, usuários da loja), *global* (ex.: catálogo de ingredientes visuais, se for do sistema) ou *plataforma* (contas, planos). Registre em tabela.
2. **Decidir o modelo** com o usuário antes de implementar (peça confirmação): banco único com `tenant_id` (mais simples), schema/banco por loja (isolamento mais forte). Considere custo, backup e migrações. Não instale pacote sem confirmar.
3. **Resolver a loja atual no servidor**: por usuário autenticado ↔ loja (admin do painel) e por identificador público validado (domínio/subdomínio/slug) para o site e `POST /pedidos`. O identificador vindo do cliente só **seleciona** a loja; a permissão sempre é verificada no backend.
4. **Escopo automático**: Global Scope/trait (`BelongsToTenant`) que filtra leituras e preenche `tenant_id` na criação; chaves únicas e `exists:` das validações precisam ser **por loja** (ex.: `exists:sabores,id` deve restringir ao tenant); relacionamentos e `whereIn` também.
5. **Autorização**: usuário pertence à loja; papéis (dono, atendente) via Policies/Gates; endpoints de plataforma separados dos da loja.
6. **Pontos fáceis de esquecer**: jobs/filas (carregar tenant no job), cache (chave com tenant), arquivos/uploads, exports e relatórios (`DashboardService`), tokens Sanctum (a habilidade/loja do token), logs, seeders, comandos Artisan, notificações.
7. **Migração dos dados atuais**: crie a loja inicial, preencha `tenant_id`, torne obrigatório depois do backfill; migrations reversíveis e em etapas.
8. **Testes de acesso cruzado (obrigatórios)**: crie duas lojas A e B com dados e usuários. Para cada recurso e cada verbo (listar, ver, criar, editar, excluir):
   - usuário de A **não** vê, altera nem apaga dado de B (esperar 404/403);
   - `POST /pedidos` com ids de sabores/bordas/bebidas/esfirras/adicionais da loja B enquanto a loja atual é A → rejeitado;
   - tentar forçar `tenant_id` no corpo da requisição → ignorado;
   - listas, contagens, dashboard e exports de A não incluem B.
   Um teste de isolamento só vale se falhar quando o escopo é removido: confirme isso (mutação rápida) e relate.

## Critérios de aprovação
Toda tabela por loja tem `tenant_id` e escopo; nenhuma regra `exists`/`unique` cruza lojas; testes de acesso cruzado verdes para todos os recursos; `php artisan test` verde; nenhum endpoint depende só de id enviado pelo cliente.

## Relatório
```
## Multi-tenancy — <data>
Modelo escolhido e justificativa
| Tabela/recurso | Tipo (loja/global/plataforma) | Escopo aplicado | Testes de acesso cruzado |
Lacunas encontradas (com arquivo:linha)
Alterações feitas (arquivos, migrations)
Testes executados e resultado
Riscos restantes
```
