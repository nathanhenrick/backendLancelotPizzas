---
name: code-review
description: Revisa mudanças de código da API Laravel do Lancelot Pizzas (diff, branch ou arquivos): bugs, regressões, segurança, preços calculados no servidor, transações e cobertura de testes. Use ao pedir revisão, antes de commit ou PR, ou para conferir uma alteração feita por outro agente.
---

# Revisão de código — API Lancelot Pizzas

Modo padrão: **somente leitura** (reportar). Corrija só se pedirem depois.

## Procedimento
1. **Delimitar**: `git status`, `git diff` (ou o intervalo/branch indicado) e leia os arquivos alterados inteiros, não só o diff. Identifique o objetivo da mudança.
2. **Arquitetura**: respeita Controller fino → Service → Model, Form Request e Resource? Regra de negócio duplicada? Há transação onde várias tabelas mudam?
3. **Correção**: lógica, casos de borda (listas vazias, `null`, ids inexistentes, itens inativos, quantidade 0), ordem de chaves após `validated()` (arrays podem sair fora de ordem), tipos, timezones, arredondamento de dinheiro (`round(..., 2)`).
4. **Segurança**: autenticação/autorização nas rotas novas, IDOR, mass assignment (`$fillable`), validação completa (tamanho, tipo, `exists`), exposição de dados nos Resources, mensagens de erro, rate limit, e **isolamento entre lojas** (veja `saas-multitenancy`).
5. **Preço e pedido**: nada do cliente define preço/total; `PedidoService` recalcula; itens desativados são recusados.
6. **Banco**: migration nova (não editar as aplicadas), reversível (`down`), índices e `nullable` coerentes; compatível com o esquema atual e com o seeder.
7. **Compatibilidade**: contrato JSON usado pelo frontend (`../montaPizza/app/types/pizza.ts`, `app/services`). Campo removido/renomeado quebra o site.
8. **Testes**: a mudança tem teste de feliz, de erro e de permissão? Rode `php artisan test` e informe o resultado real; se `vendor/bin/pint --test` existir, rode e informe. Se não rodou, diga que não rodou.
9. **Classificar** cada achado e propor correção objetiva.

## Gravidade
- **Bloqueante**: bug que quebra pedido/dados, falha de segurança, vazamento entre lojas.
- **Importante**: regressão provável, teste essencial faltando, regra duplicada arriscada.
- **Sugestão**: legibilidade, nomes, simplificação.

## Critérios de aprovação
Sem Bloqueantes; Importantes tratados ou aceitos explicitamente; testes relevantes passando.

## Relatório
```
## Revisão — <escopo> — <data>
Veredito: aprovado / aprovado com ressalvas / reprovado
Resumo do que a mudança faz
| Gravidade | Arquivo:linha | Achado | Por que importa | Correção sugerida |
Testes executados (comando e resultado) / não executados
Pontos positivos (curtos)
```
Não invente problemas: se algo está certo, diga. Cada achado precisa apontar o trecho de código.
