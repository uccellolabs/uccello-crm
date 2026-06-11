<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Persist the chosen UI language: an unencrypted cookie for everyone, and
     * the user's profile column when authenticated. The Inertia visit reloads
     * the shared props so the UI re-renders in the new language.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(SetLocale::SUPPORTED)],
        ]);

        $locale = $validated['locale'];

        $request->user()?->update(['locale' => $locale]);

        return back()->withCookie(cookie('locale', $locale, 60 * 24 * 365));
    }
}
