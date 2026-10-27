# Lancelot Pizzas — API

API do [Lancelot Pizzas](https://github.com/nathanhenrick/lancelotPizzas), o site de pedidos em que o cliente monta a pizza em 3D e envia o pedido pelo WhatsApp. Cuida do cardápio, do cálculo dos pedidos, do login e de todo o painel administrativo da pizzaria.

Feita com Laravel 13, Sanctum (token Bearer) e MySQL, no padrão Controller → Service → FormRequest → Resource, com respostas `{ status, message, data }` pelo trait `HttpResponses`.

## Rodar com Docker

O jeito mais simples é pelo repositório do site, que sobe site, API e MySQL juntos. Clone os dois lado a lado, na mesma pasta:

```bash
git clone https://github.com/nathanhenrick/lancelotPizzas.git
git clone https://github.com/nathanhenrick/backendLancelotPizzas.git
cd lancelotPizzas
docker compose up --build
```

Detalhes (portas, credenciais de demonstração, API em outra pasta) no [README do site](https://github.com/nathanhenrick/lancelotPizzas#rodar-com-docker).

O `Dockerfile` deste repositório é o que o compose usa. Ao subir, o container roda as migrations e o seeder (que não duplica o cardápio) e, se `APP_KEY` não vier do ambiente, gera uma e guarda no volume `storage/app`.

## Rodar sem Docker

Requisitos: PHP 8.3 ou mais novo, Composer e MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

- Banco: crie o banco antes do `migrate` (`CREATE DATABASE montapizza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`) e preencha `DB_USERNAME`/`DB_PASSWORD` no `.env`.
- O seeder cria o cardápio de exemplo e o primeiro acesso ao painel. Defina `ADMIN_EMAIL` e `ADMIN_SENHA` no `.env` antes do `db:seed`: sem elas nenhum administrador é criado (só os testes usam um padrão).
- Mantenha `APP_DEBUG=false`; em produção a API nem sobe com debug ligado.
- `CORS_ALLOWED_ORIGINS` lista os endereços do site que podem chamar a API (padrão: `http://localhost:3000`).

## Testes

```bash
php artisan test
```

Os testes ficam em `tests/Feature` e usam SQLite em memória, sem tocar no MySQL.

## Estrutura (`app/`)

- `Http/Controllers/`: um por recurso; só recebem, chamam o service e respondem com `success`/`error`.
- `Services/`: regras de negócio. `PedidoService` calcula preço, linhas e total do pedido.
- `Http/Requests/`: validação. `StorePedidoRequest` também confere o máximo de sabores do tamanho e se sabores, borda e bebidas estão ativos.
- `Http/Resources/`: formato das respostas (o mesmo que o site espera).
- `Http/Middleware/EnsureUserIsAdmin.php`: alias `admin`; só usuários ativos entram nas rotas do painel.

## Rotas (`/api/v1`)

Públicas (site):

| Método | Rota | O quê |
| --- | --- | --- |
| GET | `/configuracoes` | Nome, WhatsApp e regra de preço |
| GET | `/precos` | Tamanhos e preços por faixa |
| GET | `/sabores`, `/bordas`, `/bebidas`, `/esfirras`, `/adicionais` | Cardápio (só itens ativos; o dono logado vê todos) |
| GET | `/politica-privacidade` | Política de privacidade (LGPD) |
| POST | `/pedidos` | Grava o pedido (limite de 10 por minuto por IP) |
| POST | `/auth/login` | Login do painel; devolve o token |

Painel (`Authorization: Bearer <token>`):

| Método | Rota | O quê |
| --- | --- | --- |
| GET / POST | `/auth/me`, `/auth/logout` | Sessão |
| GET | `/dashboard` | Resumo do dia, últimos pedidos e sabores mais pedidos (30 dias) |
| GET | `/admin/pedidos` | Pedidos (filtro opcional `?status=`) |
| GET / PUT | `/pedidos/{id}` | Ver pedido / mudar a situação |
| PUT | `/configuracoes`, `/precos` | Salvar configurações / tamanhos e preços |
| PUT | `/politica-privacidade` | Publicar a política de privacidade |
| POST / PUT / DELETE | `/sabores`, `/bordas`, `/bebidas`, `/esfirras`, `/adicionais` | Cadastros do cardápio |
| GET / POST / PUT / DELETE | `/users` | Usuários do painel |

## Pedido

O site manda só as escolhas do cliente:

```json
{ "cliente": "Ana", "modo": "entrega", "tamanho": "g", "sabores": [1, 9], "borda": 2, "bebidas": [{ "id": 2, "quantidade": 2 }] }
```

A API calcula o preço com o cardápio salvo (regra do sabor de maior valor ou da média, conforme a configuração), guarda uma cópia dos nomes e valores do momento e devolve o pedido com número, linhas e total. Valores mandados pelo cliente são ignorados.

## Papéis e segurança

- **Papéis do painel:** `dono` faz tudo; `atendente` só vê e atualiza pedidos (`GET /admin/pedidos`, `GET`/`PUT /pedidos/{id}`). Usuário criado sem papel nasce `atendente`. O papel é lido do banco a cada requisição, e mudá-lo derruba as outras sessões da pessoa. Ninguém muda o próprio papel.
- **Cardápio público:** o site só recebe itens ativos. O dono logado vê também os desativados (é por onde o painel os reativa).
- **Pedidos:** a situação só avança (`novo` → `preparo` → `saiu` → `concluido`, ou `cancelado` a qualquer momento); `concluido` e `cancelado` são finais. A lista do painel vem paginada (`?pagina=` e `?por_pagina=`, padrão 200, máximo 500) com o bloco `meta`; usuários seguem a mesma ideia (padrão 100, máximo 200).
- **Limites de taxa:** login por IP e por e-mail; pedidos por IP e um teto geral por minuto. Atrás de proxy ou CDN, defina `TRUSTED_PROXIES`, senão todos compartilham o IP do proxy.
- **Logo da bebida:** só PNG, JPEG, WebP ou GIF válidos (SVG é recusado porque pode carregar script). Um valor igual ao já salvo é aceito, para dados antigos não travarem a edição.
- **Decisões aceitas:**
  - O e-mail do usuário é único.
  - O token vale 7 dias e não tem escopos: a autorização é o papel do usuário no banco, e o token cai ao trocar senha, papel ou ao desativar o acesso.
  - CORS libera só as origens de `CORS_ALLOWED_ORIGINS`, sem credenciais; em produção liste apenas o domínio real do site.

## Licença

Copyright © 2026 Nathan Henrick. **Todos os direitos reservados.** O código está público só para consulta e avaliação pessoal; qualquer uso comercial, cópia, modificação ou distribuição depende de autorização por escrito do autor. Veja [LICENSE](LICENSE).
