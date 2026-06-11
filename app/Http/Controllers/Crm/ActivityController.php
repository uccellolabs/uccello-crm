<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreActivityRequest;
use App\Infrastructure\Services\CrmMorph;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ActivityController extends Controller
{
    /**
     * Log a new activity against a CRM record.
     */
    public function store(StoreActivityRequest $request): RedirectResponse
    {
        Gate::authorize('create', Activity::class);

        $validated = $request->validated();

        // Resolve (and tenant-check) the parent record before attaching.
        $subject = CrmMorph::resolve($validated['subjectable_type'], (int) $validated['subjectable_id']);

        Activity::create([
            'type' => $validated['type'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'] ?? null,
            'occurred_at' => $validated['occurred_at'] ?? now(),
            'subjectable_type' => $validated['subjectable_type'],
            'subjectable_id' => $subject->getKey(),
            'user_id' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Activity logged.')]);

        return back();
    }

    /**
     * Delete a logged activity.
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        Gate::authorize('delete', $activity);

        $activity->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Activity deleted.')]);

        return back();
    }
}
