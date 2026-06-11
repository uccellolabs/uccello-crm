<?php

namespace App\Http\Middleware;

use App\Domain\Shared\Enums\TeamPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Per-request cache of the loaded translation dictionary.
     *
     * @var array<string, string>|null
     */
    protected ?array $translations = null;

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'sidebarPromo' => (bool) config('uccello.sidebar_promo.enabled'),
            'locale' => app()->getLocale(),
            'translations' => fn (): array => $this->translations(),
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'permissions' => fn (): array => [
                'manageCustomFields' => (bool) ($user?->currentTeam
                    && $user->hasTeamPermission($user->currentTeam, TeamPermission::ManageCustomFields)),
            ],
        ];
    }

    /**
     * The JSON translation dictionary for the active locale, shared with the
     * frontend. Empty for the default English keys (identity lookup).
     *
     * @return array<string, string>
     */
    protected function translations(): array
    {
        if ($this->translations !== null) {
            return $this->translations;
        }

        $path = lang_path(app()->getLocale().'.json');

        /** @var array<string, string> $messages */
        $messages = File::exists($path) ? File::json($path) : [];

        return $this->translations = $messages;
    }
}
