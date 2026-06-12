<?php

namespace App\Http\Controllers;

use App\Application\Settings\UseCases\UpdateLocale;
use App\Http\Requests\Settings\UpdateLocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Persist the chosen UI language: an unencrypted cookie for everyone, and
     * the user's profile column when authenticated. The Inertia visit reloads
     * the shared props so the UI re-renders in the new language.
     */
    public function update(UpdateLocaleRequest $request, UpdateLocale $updateLocale): RedirectResponse
    {
        $command = $request->toCommand();

        $updateLocale->handle($request->user(), $command);

        return back()->withCookie(cookie('locale', $command->locale, 60 * 24 * 365));
    }
}
