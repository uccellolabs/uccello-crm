<?php

namespace App\Http\Controllers\Crm;

use App\Application\Activities\UseCases\CreateActivity;
use App\Application\Activities\UseCases\DeleteActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreActivityRequest;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function store(StoreActivityRequest $request, CreateActivity $createActivity): RedirectResponse
    {
        Gate::authorize('create', Activity::class);

        $createActivity->handle($request->toCommand($request->user()->id));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Activity logged.')]);

        return back();
    }

    public function destroy(Activity $activity, DeleteActivity $deleteActivity): RedirectResponse
    {
        Gate::authorize('delete', $activity);

        $deleteActivity->handle($activity);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Activity deleted.')]);

        return back();
    }
}
