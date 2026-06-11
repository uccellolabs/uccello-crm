<?php

namespace App\Providers;

use App\Domain\Shared\Enums\TeamPermission;
use App\Models\Assistant;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use App\Policies\AssistantPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMorphMap();
        $this->configureGates();
        $this->configurePolicies();
    }

    protected function configureGates(): void
    {
        Gate::define('manage-pipelines', fn (User $user): bool => $user->currentTeam !== null
            && $user->hasTeamPermission($user->currentTeam, TeamPermission::ManageCustomFields));
    }

    protected function configurePolicies(): void
    {
        Gate::policy(Assistant::class, AssistantPolicy::class);
    }

    /**
     * Map polymorphic relations to stable string aliases so the database
     * stores `company` instead of the fully-qualified class name. These
     * aliases double as the `entity_type` keys for custom field definitions.
     */
    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'company' => Company::class,
            'contact' => Contact::class,
            'deal' => Deal::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
