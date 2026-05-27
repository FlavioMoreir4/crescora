# Crescora — Plano de Correção: Gaps Planejado vs Entregue

**Gerado em:** 26/05/2026
**Baseado em:** Análise Oracle + Explore do código-fonte
**Prioridade:** Pré-produção — corrigir antes de shipar

---

## Resumo Executivo

16 gaps identificados, organizados em 3 streams paralelos independentes.
Estimativa total: **~5 dias dev único** | **~2-3 dias com 2 devs paralelos**

---

## Stream A — Autorização & Segurança (1 dev, ~2 dias)

**Responsável:** Backend (Authorization + Controllers)

### A.1 — Fazer BasePolicy consumir `$resource` (⭐ principal recomendação Oracle)

**Arquivo:** `app/Domains/Shared/Policies/BasePolicy.php`

Adicionar métodos padrão que usam `$this->resource` para verificar permissões Spatie:

```php
// Adicionar ao BasePolicy:
public function viewAny(?User $user): bool
{
    $this->requireTeam();
    return $this->hasPermission("{$this->resource}.viewAny");
}

public function view(?User $user, Model $model): bool
{
    return $this->hasPermission("{$this->resource}.view")
        && $this->belongsToTeam($model);
}

public function create(?User $user): bool
{
    $this->requireTeam();
    return $this->hasPermission("{$this->resource}.create");
}

public function update(?User $user, Model $model): bool
{
    return $this->hasPermission("{$this->resource}.update")
        && $this->belongsToTeam($model);
}

public function delete(?User $user, Model $model): bool
{
    return $this->hasPermission("{$this->resource}.delete")
        && $this->belongsToTeam($model);
}
```

**Esforço:** ~15 linhas | **Risco:** Baixo (UnitPolicy sobrescreve, só afeta quem não tem método próprio)

### A.2 — Adicionar `authorizeResource()` nos controllers

**Arquivos:**
- `app/Domains/Leads/Controllers/LeadController.php`
- `app/Domains/Units/Controllers/UnitController.php`
- `app/Domains/Forms/Controllers/FormController.php`

Adicionar no construtor de cada:
```php
public function __construct()
{
    $this->authorizeResource(Lead::class, 'lead');
    // Segundo parâmetro = nome do parâmetro da rota
}
```

**Esforço:** 3 linhas | **Risco:** Médio — testar cada ação após adicionar

### A.3 — Team scope no download de export

**Arquivo:** `app/Domains/Export/Controllers/ExportController.php`

```php
// No método download(), antes de servir o arquivo:
if ($export->team_id !== TenantContext::getTeamId()) {
    abort(403);
}
```

**Esforço:** 3 linhas | **Risco:** Baixo

### A.4 — Corrigir `switchTeam()` para atualizar TenantContext

**Arquivo:** `app/Domains/Teams/Models/Concerns/HasTeams.php`

Adicionar no final de `switchTeam()`:
```php
TenantContext::setTeamId($team->id);
```

**Esforço:** 1 linha | **Risco:** Baixo

---

## Stream B — Queue & Horizon (1 dev, ~1 dia)

**Responsável:** Backend (Filas + Jobs + Notificações)

### B.1 — Agendar `horizon:snapshot`

**Arquivo:** `routes/console.php`

```php
Artisan::command('horizon:snapshot')->everyMinute();
```

**Esforço:** 1 linha | **Risco:** Baixo

### B.2 — Preencher allowlist do Horizon

**Arquivo:** `app/Providers/HorizonServiceProvider.php`

Substituir array vazio por lista de emails OU verificação de role:
```php
Gate::define('viewHorizon', function ($user) {
    return $user->hasRole(['owner', 'admin']);
    // OU: return in_array($user->email, ['admin@crescora.com.br']);
});
```

**Esforço:** 1 linha | **Risco:** Baixo

### B.3 — Adicionar `ShouldQueue` aos Listeners

**Arquivos:**
- `app/Domains/Leads/Listeners/DispatchLeadDistribution.php`
- `app/Domains/Leads/Listeners/SendLeadAssignedNotification.php`
- `app/Domains/Leads/Listeners/SendLeadStatusChangedNotification.php`

Adicionar `implements ShouldQueue, ShouldBeUniqueAfterProcessing` em cada.

**⚠️ Verificação:** `DispatchLeadDistribution` usa `DistributionService` via DI — confirmar que não depende de estado não-serializável da request. Se sim, refatorar para dispatchar um Job.

Adicionar `public $queue = 'leads';` nos listeners.

**Esforço:** ~5 linhas cada | **Risco:** Médio — testar que listeners funcionam async

### B.4 — Adicionar `ShouldQueue` às Notificações

**Arquivos:**
- `app/Domains/Notifications/Notifications/LeadAssignedNotification.php`
- `app/Domains/Notifications/Notifications/LeadStatusChangedNotification.php`

Já usam `Queueable` trait — só adicionar `implements ShouldQueue`.

Adicionar `public $queue = 'notifications';`.

**Esforço:** 1 linha cada | **Risco:** Baixo

---

## Stream C — Frontend & Infra Rápida (1 dev, ~1 dia)

**Responsável:** Fullstack (Pages + Config + Limpeza)

### C.1 — Criar `exports/Index.vue` (P1)

**Arquivo:** `resources/js/pages/exports/Index.vue`

Página de listagem de exports com tabela paginada (seguir padrão de `leads/Index.vue`). Props recebidas do `ExportController@index`:
- `exports` (paginated)
- `filters` (status filters)

### C.2 — Criar `notifications/Index.vue` (P1)

**Arquivo:** `resources/js/pages/notifications/Index.vue`

Página completa de notificações com:
- Lista paginada
- Botão "Marcar todas como lidas"
- Cada notificação clicável para marcar como lida
- Seguir padrão de layout com breadcrumbs

### C.3 — Corrigir slug nas Units (P1)

**Arquivo:** `resources/js/pages/units/Index.vue`

Trocar:
- `row.original.id` → `row.original.slug` (linha do link `href`)
- `row.original.id` → `row.original.slug` (linha do `router.visit` se houver)
- `unit.id` → `unit.slug` no `handleDelete()` se usar `router.delete()`

### C.4 — Excluir webhook billing do CSRF + assinatura

**Arquivo:** `bootstrap/app.php`

Adicionar rota de webhook ao `$except` do CSRF.

**Arquivo:** `app/Domains/Billing/Gateways/AsaasGateway.php` ou Controller

Adicionar verificação de assinatura do webhook. Asaas envia `Asaas-Signature` header com HMAC-SHA256 do body + API key.

### C.5 — Remover diretório morto

```bash
rm -rf app/Domains/Exports/
```

**Esforço:** 1 comando | **Risco:** Baixo

### C.6 — Adicionar limpeza agendada

**Arquivo:** `routes/console.php`

```php
// Limpar exports antigos (30+ dias)
Artisan::command('model:prune', ['--model' => [Export::class]])->daily();

// Limpar notificações lidas (60+ dias)
// Pode ser via comando customizado ou model:prune
```

**Esforço:** ~5 linhas | **Risco:** Baixo

### C.7 — Adicionar exception handling customizado

**Arquivo:** `bootstrap/app.php`

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (NotFoundHttpException $e) {
        return Inertia::render('errors/404')->toResponse(request());
    });

    $exceptions->render(function (AuthorizationException $e) {
        return Inertia::render('errors/403')->toResponse(request());
    });
})
```

**Esforço:** ~10 linhas | **Risco:** Baixo

---

## Resumo de Esforço por Stream

| Stream | Arquivos | Linhas | Risco | Estimativa |
|--------|----------|--------|-------|------------|
| **A** Auth & Security | 5 | ~25 linhas | Médio (testar) | ~2 dias |
| **B** Queue & Horizon | 6 | ~15 linhas | Médio (async) | ~1 dia |
| **C** Frontend & Infra | 8 | ~200 linhas | Baixo | ~1 dia |
| **Total** | 19 | ~240 linhas | — | **~4 dias** |

## Ordem Recomendada de Execução

```
Dia 1: C.1 + C.2 + C.3 (frontend) + B.1 + B.2 (Horizon) → quick wins, impacto imediato
Dia 2: A.1 + A.2 + A.3 (auth) → mais complexo, requer testes
Dia 3: B.3 + B.4 (queue) + C.4 (webhook) → dependem de Horizon funcionando
Dia 4: C.5 + C.6 + C.7 + A.4 (limpeza final)
```

---

## Verificação

Cada stream é considerado "done" quando:

- [ ] LSP diagnostics clean em todos os arquivos alterados
- [ ] `vendor/bin/pint --dirty` passou sem alterações
- [ ] `php artisan test --compact` verde
- [ ] Testes específicos da stream passam (ex: `--filter=LeadTest` para auth)
- [ ] Build Vite sem erros (`npm run build`)
