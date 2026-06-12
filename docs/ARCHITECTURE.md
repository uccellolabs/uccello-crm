# Architecture

Uccello CRM is a Laravel 13 + Inertia + Vue app organised with a **strict
hexagonal** layering. This document records the conventions so the structure
reads consistently.

## Layers & dependency direction

```
            HTTP (adapters in)                Infrastructure (adapters out)
   app/Http  ───────────────►  app/Application  ◄───────────────  app/Infrastructure
   (controllers, requests,        (use cases, commands,             (Eloquent repos/queries,
    middleware, responses)         DTOs, presenters, ports)        service impls, AI adapter)
                                          │
                                          ▼
                                    app/Domain
                              (enums, value objects, domain services,
                               repository interfaces — no i18n, no ORM)
```

Dependencies point **inward**: `Http → Application → Domain`, and
`Infrastructure → Application/Domain` (it *implements* their interfaces). The
rules, enforced by Deptrac and greps in CI/review:

- **`app/Domain` imports no Eloquent, no `Illuminate\Http`, no `App\Models`, no
  `__()`.** It is framework-agnostic (Carbon is tolerated for date value objects).
- **`app/Application` never references `App\Infrastructure`.** It depends only on
  its own ports + Domain + Eloquent models (see "Eloquent as kernel").
- **`app/Application` use cases return typed results** (`DeletionResult`,
  `OperationResult`) — never translated strings. Controllers map results to toasts
  with `__()`.
- **`app/Models` and `app/Concerns` never import `App\Application`.** Presentation
  lives in Application presenters and Infrastructure queries.
- **`app/Policies`** are HTTP guards in their standard location; they depend on
  Domain enums and Models only.

```sh
grep -rnE "use App\\Models\\|use Illuminate\\Database|use Illuminate\\Http" app/Domain   # must be empty
grep -rn  "App\\Infrastructure" app/Application                                          # must be empty
grep -rn  "App\\Infrastructure" app/Http                                                 # must be empty
grep -rnE "__\\(|->label\\(" app/Domain                                                   # must be empty
vendor/bin/deptrac analyse                                                               # layer rules
```

## Port + implementation pattern

Every cross-layer collaborator the Application/Domain needs is an **interface
(port)**; the Eloquent / framework class is the **implementation**, wired in
`app/Infrastructure/InfrastructureServiceProvider.php` (the single DI-wiring place).

This covers:

- **Repositories** — write side in `Domain\*\Repositories`, read side in
  `Application\*\Repositories` (see CQRS-lite below).
- **Queries** — `Application\*\Queries\*Interface` → `Infrastructure\*\Queries\Eloquent*`.
- **Presenters** — `Application\*\Presenters\*` map Eloquent models to DTOs; used
  by Infrastructure queries and Http middleware, never by Models/Concerns.
- **CRM services** — `Application\Crm\Services\{CustomFields,Picklists,CrmFormOptions}`
  → `Infrastructure\Services\Eloquent*` (bound `scoped` for their per-request cache).
- **AI assistant** — `Application\Assistant\Assistant` → `Infrastructure\Assistant\CrmAssistant`
  (the Laravel AI SDK adapter; the data tools + `Tool` wrappers stay in Infrastructure).
- **Team notifications** — `Infrastructure\Teams\Notifications\TeamInvitation` (mail
  adapter; triggered from `EloquentTeamRepository` when an invitation is created).

## Commands (write-side input)

Use cases that mutate state accept **readonly Command DTOs** in
`Application/{Context}/Commands/` instead of `array $data`. Form requests expose
`toCommand(): CreateXxxCommand` and controllers pass the command to the use case.

Infrastructure repositories map commands to Eloquent attributes internally.

## Read models (queries)

Read-side data is assembled by **query interfaces** in `Application\*\Queries`
returning **readonly DTOs** (list pages, show pages, form data, settings). Controllers
and middleware inject query ports — they do not call presentation methods on Eloquent
models.

Show-page DTOs include sidebar `can` flags from an authorization port or the query
itself; presenters do not call `Gate` or policies.

## Result types

Deletion and other guarded operations return enums from `Application\Shared\Results\`:

| Enum | Cases | Used when |
|------|-------|-----------|
| `DeletionResult` | `Success`, `BlockedTerminalStage`, `BlockedHasDeals` | Delete use cases |
| `OperationResult` | `Success`, `NotAllowed`, `HasDependents` | Other guarded mutations |

Domain **policies** (e.g. `Domain\Pipelines\Services\PipelineStageDeletionPolicy`)
encode business rules with pure inputs; use cases map policy output to result enums.
**Http controllers** translate enums to localized toast messages.

## Bounded contexts

| Context | Application | Domain | Infrastructure |
|---------|-------------|--------|----------------|
| CRM (companies, contacts, deals, pipelines, tasks, activities, custom fields, picklists, dashboard) | `Application\{Companies,Contacts,Deals,…}` | `Domain\{Companies,Contacts,Deals,…}` | `Infrastructure\Persistence\Eloquent\{Repositories,Queries}` |
| Teams | `Application\Teams\{UseCases,Queries,DTOs,Presenters}` | `Domain\Teams\Repositories` | `Infrastructure\Teams\{EloquentTeamRepository,Queries,Notifications}` |
| Auth | `Application\Auth\UseCases` | `Domain\Auth\Repositories` | `Infrastructure\Auth\EloquentUserRepository` |
| Settings | `Application\Settings\UseCases` | — | — |

Fortify action classes (`app/Actions/Fortify/*`) remain thin HTTP/framework adapters:
they validate input and delegate to Application use cases.

## Conventions & deliberate trade-offs

- **CQRS-lite repo split.** Write repositories live in `Domain` and speak only
  primitives (e.g. `resequence(int $stageId)`); read repositories live in
  `Application` and may return Eloquent collections for presenters. Two
  `…Repository` interfaces in two layers is intentional, not a duplicate.
- **Eloquent models are the domain entities** (`app/Models`). We do **not**
  maintain separate domain entities — that would be over-engineering for this
  CRM. Consequence: the `Domain` layer is intentionally thin (enums, value
  objects, interfaces, pure policies), and Application/Infrastructure may
  type-hint Eloquent models as the shared persistence kernel.
- **`app/Contracts/HasCrmTimeline` stays out of `Domain`** on purpose: it is
  defined in terms of Eloquent `MorphMany` relations, so moving it into `Domain`
  would reintroduce an Eloquent dependency there.
- **Tenancy is enforced at the model layer** by the `BelongsToTeam` global scope
  (`app/Concerns/BelongsToTeam.php`), keyed off the authenticated user. It is the
  one piece of "magic": queries are auto-scoped to the current team, so outside
  an HTTP request (queue jobs, console, tinker) you must set the team explicitly
  or use `withoutGlobalScope('team')`.
- **`app/Http` is the presentation layer.** There is no separate
  `app/Presentation`. Laravel **Policies stay in `app/Policies`** (their standard
  location) — they are request-level guards, not domain rules, and they delegate
  to `Domain\Shared\Enums\TeamPermission`.
- **Team validation rules** stay in `app/Rules` (framework validation adapters). They
  delegate business checks to Domain services (`TeamNamePolicy`,
  `TeamInvitationAcceptancePolicy`) and repository ports (`TeamRepositoryInterface`).
- **Enum labels** live in `Application\Shared\Presenters` (or per-context presenters)
  with `__()` — Domain enums expose values only. Role validation uses
  `TeamRole::assignableValues()` in Domain.
- **Default pipelines** are created in `CreateTeam` (onboarding) and ensured idempotently
  by read queries (`GetDealBoardQuery`, `GetPipelineSettingsQuery`,
  `GetCrmRecordFormDataQuery` for deals) — not from Http controllers.
- **CRM timeline reads** (activities/tasks on show pages) go through
  `CrmTimelineReadRepositoryInterface` — Application presenters never call Eloquent
  relations directly.

## Deptrac

Layer rules live in `deptrac.yaml` at the project root:

| Layer | May depend on |
|-------|---------------|
| Domain | Domain, Models, Framework |
| Application | Domain, Application, Models, Framework |
| Infrastructure | Domain, Application, Infrastructure, Models, Concerns, Framework |
| Models | Domain, Models, Concerns, Framework |
| Concerns | Domain, Models, Concerns, Framework |
| Policies | Domain, Models, Policies, Framework |
| Framework (`app/Http`, `app/Actions`, …) | Domain, Application, Models, Concerns, Policies, Framework |

Run `composer arch` (alias for `vendor/bin/deptrac analyse --no-progress`) locally
or in CI to verify no layer violations were introduced.

## Testing

- **Feature tests** — HTTP flows, policies, tenancy, Inertia props.
- **Unit tests** — `tests/Unit/Application/` for use cases and domain policies with
  mocked repository ports (no database). Target business-rule coverage for guarded
  operations (deletion policies, move deal, invitations, permissions).

## Production hardening

- **`SecurityHeaders` middleware** — sets `X-Frame-Options: DENY`, `Referrer-Policy`,
  and a basic CSP suitable for Inertia/Vite (registered on the web stack).
- **`TrustProxies` middleware** — trusts all proxies (`*`) for Laravel Forge /
  load-balancer deployments; prepended to the middleware stack.
- **`.env.example` production checklist** — comments for `APP_DEBUG=false`,
  `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `LOG_LEVEL=warning`.
