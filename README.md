# Singleimput

SaaS de gestão financeira para pequenos e-commerces brasileiros. Baseado em modelo de planilha DRE + Fluxo de Caixa, traduzido para uma aplicação web multi-tenant com arquitetura limpa.

---

## Stack

**Backend**
- PHP 8.3 + Laravel 11
- PostgreSQL 16 (NUMERIC para valores monetários — nunca float)
- Redis 7 + Laravel Horizon (filas e cache)
- Laravel Sanctum + 2FA TOTP

**Frontend**
- React 18 + TypeScript
- Inertia.js v2
- Vite
- shadcn/ui + Tailwind CSS

**Infraestrutura**
- Multi-tenancy schema-per-tenant no PostgreSQL
- Deploy em VPS (~3 instâncias, ~R$600–900/mês)

---

## Arquitetura

O projeto segue Clean Architecture com separação estrita de responsabilidades. Cada camada tem um papel específico e não invade a vizinha.

```
app/
├── Domain/                        # Regras de negócio puras (sem Laravel)
│   ├── BankAccounts/
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   └── Repositories/          # Interfaces (contratos)
│   ├── Categories/
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   └── Repositories/
│   ├── Transactions/
│   ├── FinancialProjections/
│   ├── BankReconciliation/
│   ├── Reports/
│   ├── Plans/
│   ├── Tenancy/
│   └── Shared/
│       └── Money.php              # Value Object monetário (bcmath)
│
├── Application/                   # Casos de uso
│   ├── BankAccounts/
│   │   ├── DTOs/
│   │   └── Handlers/
│   ├── Categories/
│   ├── Transactions/
│   ├── FinancialProjections/
│   ├── BankReconciliation/
│   ├── Reports/
│   ├── Auth/
│   │   └── TwoFactor/
│   └── Tenancy/
│
├── Infrastructure/                # Implementações concretas
│   ├── Persistence/
│   │   └── Eloquent/              # Repositories Eloquent + Models
│   ├── Tenancy/
│   │   ├── TenantContext.php
│   │   ├── TenantSchemaManager.php
│   │   └── InitialTenantAdminCreator.php
│   └── Jobs/
│
└── Http/                          # Camada HTTP (Controllers finos)
    ├── Controllers/
    │   └── Auth/
    ├── Middleware/
    │   ├── TenantMiddleware.php
    │   ├── SecurityHeadersMiddleware.php
    │   └── AuditLogMiddleware.php
    └── Requests/

resources/js/
├── Pages/
│   ├── Auth/
│   ├── BankAccounts/
│   ├── Categories/
│   ├── Transactions/
│   ├── FinancialProjections/
│   ├── BankReconciliation/
│   └── Reports/
├── Components/
│   └── ui/                        # shadcn/ui components
├── Layouts/
│   └── AuthenticatedLayout.tsx
├── types/
└── Utils/
    ├── formatCurrency.ts
    └── formatDate.ts
```

---

## Fluxo de uma requisição

```
HTTP Request
    ↓
FormRequest         (valida formato dos dados)
    ↓
Controller          (fino: converte Request → DTO, chama Handler)
    ↓
Handler             (caso de uso: orquestra Domain + Repositories)
    ↓
Domain Entity       (aplica regras de negócio)
    ↓
Repository Interface → Eloquent (persiste no banco)
    ↓
HTTP Response (via Inertia)
```

---

## Módulos

| Módulo | Status |
|---|---|
| BankAccounts (Contas Bancárias) | ✅ |
| Categories (Categorias) | ✅ |
| Transactions (Transações) | ✅ |
| FinancialProjections (Projeções) | ✅ |
| BankReconciliation (Conciliação) | ✅ |
| Reports / DRE | ✅ |
| Autenticação + 2FA TOTP | ✅ |
| Multi-tenancy (schema-per-tenant) | ✅ |
| Plans / Subscriptions | ✅ |
| Audit Log | ✅ |

---

## Regras absolutas

- **Nunca usar float para dinheiro.** Todo valor monetário passa pelo `App\Domain\Shared\Money` que usa `bcmath`.
- **Controllers sem lógica de negócio.** Controllers apenas recebem, delegam ao Handler e devolvem resposta.
- **Interfaces no Domain, implementações na Infrastructure.** O Domain não conhece Eloquent.
- **DTOs em `/DTOs/`, nunca em `/Data/`.** Convenção de namespace consistente em todos os módulos.
- **`declare(strict_types=1)` em todos os arquivos PHP.**
- **Controllers no plural** — `BankAccountsController`, `CategoriesController`.

---

## Instalação

```bash
# Clonar e instalar dependências
git clone <repo>
cd singleimput
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Configurar banco (PostgreSQL)
# Editar .env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Migrations
php artisan migrate

# Frontend
npm run build

# Horizon (filas)
php artisan horizon
```

---

## Testes

```bash
# Todos os testes
php artisan test

# Apenas unit tests
php artisan test --testsuite=Unit

# Apenas feature tests
php artisan test --testsuite=Feature
```

Os testes de unidade usam `InMemoryRepository` para rodar sem banco de dados. Os testes de feature usam `RefreshDatabase` com SQLite `:memory:`.

---

## Multi-tenancy

Cada tenant (empresa cliente) possui um schema próprio no PostgreSQL. O `TenantMiddleware` identifica o tenant pela requisição e injeta o schema correto. Todos os models de tenant aplicam `TenantDataScope` automaticamente via GlobalScope.

Para criar um novo tenant:

```bash
php artisan tenant:create --name="Empresa X" --email="admin@empresa.com"
```

---

## Origem do modelo financeiro

O sistema é baseado em uma planilha de modelagem financeira (DRE + Fluxo de Caixa) com as seguintes fórmulas centrais:

- **Receita Bruta** = Investimento em tráfego pago × ROAS
- **Custo de Produção** = 33% da Receita Bruta
- **Intermediadores de Pagamento** = 4,5% da Receita Bruta
- **Frete** = 8% da Receita Bruta
- **Tributos** = 4,5% a 5,5% (progressivo por faixa de faturamento)
- **EBITDA** = Receita Líquida − Custo Operacional − Despesa Operacional
- **Saldo diário** = calculado por SUMIF de data e banco na base de dados diária