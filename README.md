# Auth Seguro

Sistema de cadastro, login e área restrita em Laravel 12, com foco em segurança
seguindo recomendações da OWASP. Sem Breeze/Jetstream/Fortify — controllers,
form requests, middleware e views escritos manualmente e comentados em
português, pra cada decisão de segurança ficar explícita e auditável.

## Stack

- PHP 8.2+ (Laravel 12 exige no mínimo 8.2)
- Laravel 12
- MySQL/MariaDB via Eloquent/PDO (prepared statements nativamente)
- Composer

## Requisitos antes de instalar

- PHP 8.2+ com as extensões: `pdo_mysql`, `openssl`, `mbstring`, `curl`, `fileinfo`, `zip`
- Composer
- MySQL 8+ ou MariaDB rodando localmente (ou acessível via rede)
- Um CA bundle válido configurado em `curl.cainfo`/`openssl.cafile` no `php.ini`
  (necessário pra `Password::uncompromised()` conseguir verificar senhas vazadas
  via HTTPS na API do Have I Been Pwned). Em distros Linux normalmente já vem
  configurado; no Windows pode ser preciso baixar o bundle de
  [curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem) e apontar pra ele.

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edite o `.env` com as credenciais do seu banco (`DB_DATABASE`, `DB_USERNAME`,
`DB_PASSWORD`) e do SMTP (`MAIL_*`) — veja `database/schema.sql` se preferir
criar o banco e o usuário manualmente em vez de deixar o Laravel criar via
migration.

```bash
php artisan migrate
php artisan serve
```

Acesse `http://localhost:8000`.

### E-mail em desenvolvimento

Pra não precisar de um SMTP real durante o desenvolvimento, defina no `.env`:

```
MAIL_MAILER=log
```

Os e-mails de verificação de cadastro e redefinição de senha aparecem em
`storage/logs/laravel.log` (procure pelo link `http://.../verificar-email/...`
ou `http://.../redefinir-senha/...`).

## Rodando os testes

```bash
php artisan test
```

A suíte cobre: cadastro (senha forte, e-mail duplicado, senha vazada),
login (credenciais corretas/erradas com mensagem genérica), brute force
(bloqueio e desbloqueio por janela de tempo), logout, verificação de
e-mail (link assinado válido/expirado/adulterado), redefinição de senha
(resposta idêntica pra e-mail existente/inexistente) e proteção do
dashboard.

Os testes usam SQLite em memória (configurado em `phpunit.xml`) — as
extensões `pdo_sqlite` e `sqlite3` precisam estar habilitadas no `php.ini`
só pra rodar a suíte; a aplicação em si usa MySQL.

## Estrutura do projeto

```
app/
├── Http/
│   ├── Controllers/Auth/   — RegisterController, LoginController, LogoutController,
│   │                         EmailVerificationController, PasswordResetController
│   ├── Controllers/        — DashboardController
│   ├── Requests/Auth/      — Form Requests com toda a validação server-side
│   └── Middleware/         — SecurityHeaders, InactivityTimeout
├── Models/                 — User, LoginAttempt
└── Services/               — BruteForceProtectionService

config/
├── hashing.php   — argon2id como driver de hash
└── security.php  — limites de brute force e timeout de inatividade

database/
├── migrations/   — fonte da verdade do schema (rode `php artisan migrate`)
└── schema.sql    — script SQL equivalente, como referência

routes/
├── auth.php  — rotas de autenticação
└── web.php   — dashboard + inclusão de auth.php
```

## Checklist de segurança

Implementado:

- [x] Hash de senha com **argon2id** (`config/hashing.php`), `password_verify()`
      via `Hash::check()`, rehash automático no login feito pelo cast
      `'password' => 'hashed'` do Eloquent
- [x] Política de senha: mínimo 12 caracteres + checagem contra vazamentos
      conhecidos (`Password::uncompromised()`, API Have I Been Pwned, k-anonymity)
- [x] 100% acesso a banco via Eloquent/Query Builder com binding parametrizado
      (zero SQL concatenado)
- [x] Escape de saída via Blade (`{{ }}` = `htmlspecialchars` automático,
      `ENT_QUOTES`/UTF-8) — nunca `{!! !!}` com dado de usuário
- [x] CSRF: `@csrf` em todos os formulários, `VerifyCsrfToken` nativo
      (`random_bytes` + `hash_equals` internamente)
- [x] Sessões seguras: `HttpOnly`, `Secure` (produção), `SameSite=Strict`,
      `session()->regenerate()` pós-login, timeout de inatividade
      (`InactivityTimeout` middleware) além do lifetime absoluto
- [x] Proteção contra brute force: tabela própria `login_attempts`,
      bloqueio por e-mail (5/15min) e por IP (20/15min), header `Retry-After`
- [x] Validação server-side completa (Form Requests), nunca confiando em
      validação client-side
- [x] Mensagens de erro genéricas no login e no "esqueci minha senha"
      (sem enumeração de contas)
- [x] Headers de segurança: CSP, `X-Frame-Options`, `X-Content-Type-Options`,
      `Referrer-Policy`, `Permissions-Policy`, HSTS condicional a HTTPS
- [x] Exceptions nunca expostas ao usuário (`APP_DEBUG=false` em produção,
      log interno via `config/logging.php`)
- [x] Verificação de e-mail por link assinado (signed URL nativa do Laravel)
- [x] Fluxo "esqueci minha senha" completo

Fora do escopo (decisão deliberada, ver comentários no código):

- [ ] Verificação 'dns' do e-mail (`email:dns`) — descartada por depender de
      `dns_get_record()`, com suporte inconsistente em PHP no Windows
- [ ] 2FA / MFA — não foi pedido no requisito original
- [ ] Rate limiting global por IP em todas as rotas (só login tem proteção
      dedicada; reenvio de verificação usa `throttle:6,1` nativo)
