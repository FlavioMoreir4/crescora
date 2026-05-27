# Crescora

Sistema de gestão de leads com formulários dinâmicos, pipeline de distribuição e integração com Asaas.

## Stack

| Camada | Tecnologia |
|--------|-----------|
| **Backend** | Laravel 13 + PHP 8.5 |
| **Frontend** | Vue 3 + Inertia v3 + Tailwind CSS v4 |
| **Fila** | Redis + Laravel Horizon |
| **Auth** | Laravel Fortify (email, 2FA, passkeys) |
| **Banco** | SQLite (dev) / MySQL (prod) |
| **Pagamento** | Asaas (PIX) |
| **Testes** | Pest 4 |

## Requisitos

- [lerd](https://github.com/lerd/cli) — ambiente PHP local
- Docker/Podman (gerenciado pelo lerd)
- Node.js 20+

## Setup

```bash
# 1. Registrar o projeto no lerd
lerd site link

# 2. Instalar dependências
lerd composer install
npm install

# 3. Configurar ambiente
cp .env.example .env
lerd env setup

# 4. Rodar migrações e setup inicial
lerd setup

# 5. Gerar tipos Wayfinder (rotas TypeScript)
php artisan wayfinder:generate

# 6. Iniciar Horizon (filas)
lerd horizon start

# 7. Iniciar Vite (dev frontend)
npm run dev
```

Acessar: `https://crescora.test`

## Estrutura

```
app/
├── Domains/
│   ├── Billing/       # Planos e assinaturas (Asaas)
│   ├── Exports/       # Exportação de leads (CSV, XLSX, PDF, DOCX)
│   ├── Forms/         # Formulários dinâmicos + submissão pública
│   ├── Leads/         # Gestão de leads, pipeline, distribuição
│   ├── Notifications/ # Notificações in-app
│   ├── Shared/        # Base classes, traits
│   └── Units/         # Unidades organizacionais
├── Http/Controllers/  # DashboardController
├── Models/            # User, Team (Jetstream-style)
└── Providers/         # App, Fortify, Horizon service providers

resources/js/
├── pages/             # Páginas Inertia
├── components/        # Componentes Vue (shadcn-vue)
├── routes/            # Wayfinder (auto-generado)
├── layouts/           # AppLayout, Sidebar
├── types/             # Tipos TypeScript
└── lib/               # Utilitários

routes/
├── web.php            # Rotas principais
├── settings.php       # Configurações do usuário
└── console.php        # Comandos agendados

tests/
├── Feature/
│   ├── Billing/
│   ├── Exports/
│   ├── Forms/
│   ├── Leads/
│   ├── Notifications/
│   └── Units/
└── Unit/
```

## Filas (Horizon)

| Fila | Supervisor | Timeout | Descrição |
|------|-----------|---------|-----------|
| `default` | supervisor-default | 60s | Tarefas gerais |
| `leads` | supervisor-leads | 120s | Distribuição e processamento de leads |
| `exports` | supervisor-exports | 600s | Exportação de relatórios |
| `notifications` | supervisor-notifications | 60s | Notificações in-app |
| `billing` | supervisor-billing | 120s | Faturamento e cobrança |
| `media` | supervisor-media | 300s | Conversão de mídia |

## Testes

```bash
# Todos os testes
php artisan test --compact

# Feature específica
php artisan test --compact --filter="LeadTest"
```

## Comandos úteis

```bash
# Gerar rotas TypeScript (após adicionar/modificar rotas)
php artisan wayfinder:generate

# Formatar código
vendor/bin/pint --dirty

# Horizon dashboard
php artisan horizon:snapshot  # (se precisar forçar métricas)
```
