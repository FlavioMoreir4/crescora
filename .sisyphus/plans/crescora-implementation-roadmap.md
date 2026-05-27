# Crescora — Implementation Roadmap

> Gerado em: 2026-05-26
> Base: Crescora.md (master prompt) + análise de 4 agentes (Oracle, Explore, Librarian, Plan)

---

## Status Atual (Fresh Laravel 13 Starter Kit)

### O que já existe
- ✅ Laravel 13 + Inertia.js + Vue 3 + TypeScript
- ✅ Fortify Auth (Passkeys, 2FA)
- ✅ Teams (Owner/Admin/Member, slug, invitations)
- ✅ 22 shadcn-vue components (new-york-v4, Neutral)
- ✅ ~30 Pest tests (Auth, Settings, Teams)
- ✅ Tailwind CSS v4 + dark mode
- ✅ Wayfinder (TypeScript route helpers)

### O que NÃO existe (precisa construir)
- ❌ `app/Domains/` — estrutura plana `app/`
- ❌ Spatie Permission, Activitylog, Media Library, Laravel Data
- ❌ API routes
- ❌ Events/Jobs/Listeners
- ❌ Horizon (queue driver = database)
- ❌ Units, Leads, Forms, Billing, Landing Pages
- ❌ Broadcasting / Reverb / WebSockets

---

## Arquitetura Validada (pelo Oracle)

### Estrutura de Domínios Final

```
app/Domains/
├── Shared/       # BaseModel, BelongsToTeam, TenantScope, BasePolicy, DTOs
├── Teams/        # Refatorado do starter kit + Spatie Permission
├── Users/        # Refatorado — User model
├── Units/        # CRUD, slug por team, metadata JSON
├── Leads/        # Pipeline, ownership, status history, distribuição
├── Forms/        # Builder dinâmico, fields, submissions
├── LandingPages/ # Páginas públicas (slug da unit + form)
├── Notifications/# Templates, canais, preferências
├── Audit/        # Activitylog config, retention
├── Workflows/    # Fase 2 — automações condicionais
├── Billing/      # Fase 2 — gateway abstraction + PIX
└── Integrations/ # Fase 3+
```

### Decisões Arquiteturais Chave

| Decisão | Escolha | Rationale |
|---------|---------|-----------|
| Multi-tenancy | Single DB + `team_id` + Global Scopes | Escala para centenas de tenants |
| Permission | Spatie Permission com `team_foreign_key` | Nativo per-team, cacheável |
| Form data | JSON column em `form_submissions` | Flexível, JSONB indexes no MySQL |
| Billing | `GatewayInterface` própria | Cashier não suporta PIX |
| Distribuição | Strategy Pattern + async jobs | Extensível, não bloqueante |
| Eventos | Laravel Events + Horizon queues | 6 filas separadas |
| Frontend | `modules/{domain}/` | Espelha backend |

---

## Fases de Implementação

### Wave 1 — Foundation (paralelo)

| # | Task | Descrição | Depende | Skill |
|---|------|-----------|---------|-------|
| 1.1 | Instalar Spatie packages | permission, activitylog, media-library, laravel-data, query-builder | Nenhuma | `context7-mcp` |
| 1.2 | Criar diretórios | `app/Domains/*`, `resources/js/modules/*` | Nenhuma | — |
| 1.7 | Test infrastructure | Helpers, factories, base test cases | Nenhuma | — |

### Wave 2

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 1.3 | Shared Domain | BaseModel, BelongsToTeam trait, BasePolicy, TenantScope, DTOs | 1.2 |

### Wave 3

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 1.4 | Multi-tenancy | TenantContext singleton, IdentifyTenant middleware, Global Scopes | 1.3 |

### Wave 4 (paralelo)

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 1.5 | Users → Domains/Users | Refatorar User model + alias | 1.3, 1.4 |
| 1.6 | Teams → Domains/Teams | Refatorar + Spatie Permission integration | 1.3, 1.4 |

### Wave 5 (paralelo)

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 2.1 | Units CRUD | Model, migration, controller, routes, pages, tests | 1.5, 1.6, 1.7 |
| 2.3 | Dynamic Forms | Form builder + submissions + public rendering | 1.5, 1.6, 1.7 |

### Wave 6

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 2.2 | Leads Pipeline | CRUD + status history + ownership + timeline | 2.1 |
| 2.4 | Landing Pages | Rotas públicas /form/{team}/{unit} | 2.2, 2.3 |

### Wave 7 (paralelo)

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 3.1 | Distribuição | Round robin + by_unit + availability strategies | 2.2, 1.6 |
| 3.2 | Horizon + Eventos | Configurar queues + event flow | 3.1 |
| 3.4 | Exportação | CSV/XLSX async com column selection | 2.2, 2.3 |

### Wave 8

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 3.3 | Notificações | Email + in-app | 3.2 |

### Wave 9-10 — Billing

| # | Task | Descrição | Depende |
|---|------|-----------|---------|
| 4.1 | GatewayInterface | Asaas + Pagarme + Stripe adapters | 1.6 |
| 4.2 | Plans + Subscriptions | Planos, assinaturas, feature gating | 4.1 |
| 4.3 | PIX Recorrente | Integração Asaas PIX Automático | 4.2 |
| 4.4 | Webhooks | Processamento de eventos do gateway | 4.3 |

### Wave 11 — Frontend

| # | Task | Descrição |
|---|------|-----------|
| 5.1 | DataTable | Tabela reutilizável com filtros server-side |
| 5.2 | Dashboards | KPIs, gráficos, métricas |
| 5.3 | Dark Mode Polish | Consistência em componentes custom |
| 5.4 | Animações | Transições, skeleton loaders, optimistic UI |

### Wave 12 — Production

| # | Task | Descrição |
|---|------|-----------|
| 6.1 | Test Suite Completo | Cobertura unit + feature + browser |
| 6.2 | Documentação | ARCHITECTURE.md, API.md, ADRs |
| 6.3 | CI/CD | GitHub Actions |
| 6.4 | Deploy | Docker + Forge/Ploi |

---

## Estratégia de Commits (27 commits atômicos)

```
 1  chore: install spatie packages (permission, activitylog, media, data)
 2  feat: scaffold app/Domains directory structure
 3  feat: add Shared domain (BaseModel, BasePolicy, DTOs, concerns)
 4  feat: implement multi-tenancy layer (global scopes, Tenant, middleware)
 5  refactor: move User to Domains/Users
 6  refactor: move Teams to Domains/Teams + Spatie permissions
 7  test: add testing infrastructure and domain factories
 8  feat: add Units domain with CRUD and tenant isolation
 9  feat: add Leads domain with pipeline, status history, ownership
10  feat: add Dynamic Forms builder
11  feat: add public landing pages
12  feat: add lead distribution engine
13  feat: configure Horizon and event-driven queues
14  feat: add notification system
15  feat: add async export system (CSV/XLSX)
16  feat: add billing gateway abstraction
17  feat: add subscription plans
18  feat: add PIX recorrente integration
19  feat: add webhook handling
20  feat: add reusable DataTable component
21  feat: add dashboard with KPI aggregates
22  style: polish dark mode across components
23  feat: add animations and skeleton loaders
24  test: comprehensive test suite
25  docs: add architecture, API, deployment docs
26  ci: add GitHub Actions pipeline
27  chore: production deployment configuration
```

---

## Riscos Críticos

| Risco | Impacto | Mitigação |
|-------|---------|-----------|
| Vazamento de tenant (Global Scope falho) | 🔴 Crítico | TenantContext + testes de isolamento em TODO model |
| Big Ball of Mud (domínios acoplados) | 🟠 Alto | Domínios comunicam via Events + DTOs apenas |
| Performance EAV em escala | 🟠 Médio→Alto | Materialized views + índices parciais + cache |
| Billing webhook idempotência | 🟡 Médio | Unique constraint em gateway_event_id |

---

## Observações do Oracle

1. **Multi-tenancy**: Usar `TenantContext` singleton + `IdentifyTenant` middleware. Jobs recebem `$teamId` no constructor.
2. **Forms**: JSON column em `form_submissions.data` + `value_type` em `form_submission_values` para cast correto.
3. **Billing**: Não usar Cashier. Interface mínima com 5 métodos. Asaas como driver primário.
4. **Frontend**: Composables sobre Pinia. Inertia shared data para sessão. shadcn-vue como primitive layer.
5. **Domínios**: Não mover tudo. Começar com novos domínios (Units, Leads, Forms). Manter `app/Models/User.php` como alias.

---

## Observações do Librarian

1. **Modular Monolith**: Preferir `app/Modules/` sem package extra (mais limpo que nwidart/laravel-modules)
2. **Queue Tenant Isolation**: Usar `rylxes/laravel-tenant-jobs` ou passar `$teamId` escalar no payload
3. **Form Builder**: Field Snapshot pattern para acurácia histórica
4. **Round Robin**: `DB::transaction()` + `lockForUpdate()` para evitar race conditions
5. **Asaas**: `lumensistemas/asaas-laravel` é o package mais completo (typed DTOs, Cashier-style)
6. **Spatie Data**: Cache em produção via `php artisan data:cache` + TypeScript generation
