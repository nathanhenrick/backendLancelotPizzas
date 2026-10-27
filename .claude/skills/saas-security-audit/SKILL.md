---
name: saas-security-audit
description: Auditoria de segurança da API Laravel/Sanctum do Lancelot Pizzas, com evidências, classificação de risco e testes recomendados. Use para auditar endpoints, autenticação, autorização, validação, CORS, rate limiting, exposição de dados, mass assignment, IDOR/BOLA, ou antes de abrir o SaaS a novos clientes.
---

# Auditoria de segurança da API

Modo padrão: **somente leitura**. Não altere código a menos que peçam correções depois do relatório.

## Procedimento
1. **Inventário**: liste todas as rotas (`php artisan route:list --path=api`) e classifique: pública, autenticada (`auth:sanctum`), admin (`admin`). Compare com `routes/api.php`.
2. **Autenticação**: fluxo de login (`AuthService`, `AuthController`): comparação de senha, mensagens de erro (enumeração de usuário), `throttle:login`, expiração/revogação de tokens (`config/sanctum.php`), logout, troca de senha invalidando sessões (`UserService`).
3. **Autorização**: para cada endpoint não público, quem pode chamar? O middleware `EnsureUserIsAdmin` só checa usuário ativo: existe distinção de papéis? Um usuário pode agir sobre recurso de outro (IDOR/BOLA)? Onde falta `Policy`/`Gate`, registre.
4. **Validação e mass assignment**: leia cada `Form Request`; compare com `$fillable` dos Models; procure campos aceitos que o cliente não deveria controlar (preço, status, ids, `ativo`).
5. **Regras de negócio sensíveis**: `PedidoService` — preço e total vêm do banco? Quantidades, limites, itens inativos, estado do pedido (`status`). Pedido pode ser lido/alterado por quem?
6. **Exposição de dados**: Resources devolvem só o necessário? `GET /pedidos/{id}` e listas administrativas exigem admin? Logs e erros vazam `getMessage()` (os controllers devolvem `$e->getMessage()` em 500 — verifique o que isso expõe)? Dados de logo/imagem (`data:` URL): tamanho e tipo validados?
7. **Transporte e CORS**: `config/cors.php` (origens por env, `supports_credentials`), cabeçalhos, `APP_DEBUG` e `APP_ENV` em produção (`.env.example` só como referência).
8. **Abuso**: rate limits (`login`, `pedidos`, `throttle:60,1`), criação de pedidos em massa, upload/payload grande, enumeração de ids.
9. **Dependências**: `composer audit` se disponível (verifique antes de afirmar; registre se não rodou).
10. **Multi-tenancy**: registre o que impediria o isolamento hoje (ver `saas-multitenancy`).
11. **Confirmar com teste**: para cada achado relevante, descreva (ou escreva em arquivo separado, se pedirem) um teste PHPUnit que reproduz. Nunca teste contra produção.

## Classificação
- **Crítico**: acesso/alteração de dados de outras lojas, bypass de autenticação, execução de código.
- **Alto**: IDOR, escalada de privilégio, vazamento de dados pessoais, preço manipulável.
- **Médio**: falta de rate limit em fluxo sensível, mensagens de erro verbosas, configuração insegura.
- **Baixo/Informativo**: endurecimentos e boas práticas.
Separe **comprovado** (reproduzido ou lido no código com evidência) de **hipótese**.

## Critérios de aprovação
Nenhum Crítico/Alto aberto; cada Médio com plano; todos os endpoints classificados no inventário; testes recomendados listados.

## Relatório
```
## Auditoria de segurança — <data> — commit <hash>
Resumo executivo (3-5 linhas) e contagem por gravidade
| ID | Gravidade | Estado (comprovado/hipótese) | Local (arquivo:linha / rota) | Evidência | Impacto | Correção recomendada | Teste sugerido |
Inventário de rotas (público / autenticado / admin)
O que foi verificado e como (comandos)
O que NÃO foi verificado
```
