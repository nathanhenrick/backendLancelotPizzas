<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>


# Lancelot Pizzas — Backend (API SaaS)

API do site de pedidos de pizzaria, consumida pelo frontend Nuxt 4 (`../montaPizza`). Evolui para um SaaS multi-tenant.

## Stack e estado real (confira antes de assumir)

- Laravel 13, PHP, Laravel Sanctum (token Bearer), MySQL em desenvolvimento; testes com PHPUnit (`php artisan test`, em `tests/Feature`); formatação com Pint disponível (`vendor/bin/pint`).
- Respostas no padrão `{ status, message, data }` (trait `HttpResponses`); recursos em `app/Http/Resources`.
- Arquitetura atual: `Controllers` finos → `Services` (regra de negócio, transações) → `Models`; validação em `Form Requests` (`app/Http/Requests/<Recurso>/`). Não há Policies/Gates hoje: a autorização é o middleware `admin` (`EnsureUserIsAdmin`, exige usuário `ATIVO`) nas rotas de escrita em `routes/api.php`.
- Rotas públicas (só leitura do cardápio e criação de pedido, com `throttle:pedidos`); login com `throttle:login`; CORS por `CORS_ALLOWED_ORIGINS` em `config/cors.php`.
- **Multi-tenancy ainda não existe**: o sistema atende uma única pizzaria (tabela `configuracoes` com registro único, sem `tenant_id` nas tabelas). Qualquer trabalho de SaaS começa por desenhar o isolamento (skill `saas-multitenancy`), não por supor que ele existe.

## Regras de desenvolvimento

- Inspecione a arquitetura e os arquivos envolvidos antes de editar; siga os padrões existentes (Controller → Service → Model, Form Request, Resource).
- Preços, totais e descontos são sempre recalculados no servidor (`PedidoService`); nunca aceite valores do cliente.
- Use transações (`DB::transaction`) quando mais de uma tabela mudar.
- Migrations novas para qualquer mudança de esquema; não edite migration já aplicada.
- Rode `php artisan test` após alterações e não declare testes aprovados sem executá-los. Não enfraqueça validação, autorização ou teste para fazer algo passar.

## Multi-tenancy (alvo do SaaS)

- Cada pizzaria tem dados isolados: leitura, criação, edição, exclusão, relacionamentos, jobs, exports e relatórios.
- A pizzaria atual é resolvida no backend por mecanismo validado (ex.: usuário autenticado ↔ loja, ou domínio/slug verificado). Nunca confie só em `tenant_id` enviado pelo cliente.
- Toda feature nova precisa de teste de acesso cruzado entre lojas.

## Segurança

- Autenticação e autorização em todo endpoint não público; revise rate limiting, CORS e mass assignment (`$fillable`).
- Proteja contra IDOR/BOLA, SQL injection e XSS; não exponha senhas, tokens, segredos ou dados pessoais em respostas e logs.
- Não execute nada destrutivo contra produção. Diferencie vulnerabilidade comprovada de hipótese.

## Processo

- Explique decisões arquiteturais relevantes; ao terminar, informe arquivos alterados, testes executados e riscos restantes.
- Em auditorias, não altere código: entregue evidências (arquivo, linha, requisição), gravidade e testes recomendados. Não declare auditoria concluída sem evidência verificável.

## Skills deste repositório (`.claude/skills/`)

- `saas-security-audit` — auditoria de segurança da API com evidências e classificação de risco.
- `saas-multitenancy` — desenho e verificação do isolamento entre pizzarias, com testes de acesso cruzado.
- `code-review` — revisão de mudanças (regressões, segurança, testes).
