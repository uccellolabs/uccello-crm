<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active locale for the request: the authenticated user's saved
 * preference wins, then a `locale` cookie (guests / pre-login), then the app
 * default. Bounded to the supported set.
 */
class SetLocale
{
    /** @var list<string> */
    public const SUPPORTED = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        // optional() keeps this guest-safe: $request->user() is null for guests
        // (the auth middleware does not run before this in the web group).
        $locale = optional($request->user())->locale
            ?? $request->cookie('locale')
            ?? config('app.locale');

        if (! is_string($locale) || ! in_array($locale, self::SUPPORTED, true)) {
            $locale = (string) config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
