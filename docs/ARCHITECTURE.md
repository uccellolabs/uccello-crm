# Architecture

Uccello CRM is a Laravel 13 + Inertia + Vue app organised with a pragmatic
**DDD-lite / hexagonal-lite** layering for the CRM domain. This document records
the conventions so the structure reads consistently.

## Layers & dependency direction

```
            HTTP (adapters in)                Infrastructure (adapters out)
   app/Http  ───────────────►  app/Application  ◄───────────────  app/Infrastructure
   (controllers, requests,        (use cases, DTOs,                (Eloquent repos/queries,
    middleware, responses)         presenters, ports)              service impls, AI adapter)
                                          │
                                          ▼
                                    app/Domain
                              (enums, value objects,
                               repository interfaces)
```

Dependencies point **inward**: `Http → Application → Domain`, and
`Infrastructure → Application/Domain` (it *implements* their interfaces). The
rules, enforced by two greps in CI/review:

- **`app/Domain` imports no Eloquent, no `Illuminate\Http`, no `App\Models`.** It
  is framework-agnostic (Carbon is tolerated for date value objects).
- **`app/Application` never references `App\Infrastructure`.** It depends only on
  its own ports + Domain + Eloquent models (see "Eloquent as kernel").

```sh
grep -rnE "use App\\Models\\|use Illuminate\\Database|use Illuminate\\Http" app/Domain   # must be empty
grep -rn  "App\\Infrastructure" app/Application                                          # must be empty
```

## Port + implementation pattern

Every cross-layer collaborator the Application/Domain needs is an **interface
(port)**; the Eloquent / framework class is the **implementation**, wired in
`app/Providers/ArchitectureServiceProvider.php` (the single DI-wiring place).

This covers:

- **Repositories** — write side in `Domain\*\Repositories`, read side in
  `Application\*\Repositories` (see CQRS-lite below).
- **Queries** — `Application\*\Queries\*Interface` → `Infrastructure\Persistence\Eloquent\Queries\Eloquent*`.
- **CRM services** — `Application\Crm\Services\{CustomFields,Picklists,CrmFormOptions}`
  → `Infrastructure\Services\Eloquent*` (bound `scoped` for their per-request cache).
- **AI assistant** — `Application\Assistant\Assistant` → `Infrastructure\Assistant\CrmAssistant`
  (the Laravel AI SDK adapter; the data tools + `Tool` wrappers stay in Infrastructure).

## Conventions & deliberate trade-offs

- **CQRS-lite repo split.** Write repositories live in `Domain` and speak only
  primitives (e.g. `resequence(int $stageId)`); read repositories live in
  `Application` and may return Eloquent collections for presenters. Two
  `…Repository` interfaces in two layers is intentional, not a duplicate.
- **Eloquent models are the domain entities** (`app/Models`). We do **not**
  maintain separate domain entities — that would be over-engineering for this
  CRM. Consequence: the `Domain` layer is intentionally thin (enums, value
  objects, interfaces), and Application/Infrastructure may type-hint Eloquent
  models as the shared persistence kernel.
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

## Scope boundary: CRM vs starter-kit

DDD layering applies to the **CRM** (companies, contacts, deals, pipelines,
tasks, activities, custom fields, dashboard, assistant). The **auth/teams**
plumbing inherited from the starter kit is intentionally **left in classic
Laravel style** and is *not* migrated:

`app/Actions/{Fortify,Teams}`, `app/Data` (team UI DTOs), `app/Rules` (team
validation), `app/Notifications`, the auth/team traits in `app/Concerns`
(`HasTeams`, `BelongsToTeam`, `GeneratesUniqueTeamSlugs`, password/profile rules),
and `app/Policies`.

This boundary is deliberate — touching the auth/teams scaffolding buys little and
risks regressions in authentication.
