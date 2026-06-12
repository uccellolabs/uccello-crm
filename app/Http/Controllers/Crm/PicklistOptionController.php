<?php

namespace App\Http\Controllers\Crm;

use App\Application\Picklists\Queries\ListPicklistAdminQueryInterface;
use App\Application\Picklists\UseCases\CreatePicklistOption;
use App\Application\Picklists\UseCases\DeletePicklistOption;
use App\Application\Picklists\UseCases\ReorderPicklistOptions;
use App\Application\Picklists\UseCases\UpdatePicklistOption;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\SavePicklistOptionRequest;
use App\Http\Requests\ReorderIdsRequest;
use App\Models\PicklistOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PicklistOptionController extends Controller
{
    public function __construct(
        private readonly ListPicklistAdminQueryInterface $listPicklists,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', PicklistOption::class);

        return Inertia::render('crm/picklists/Index', $this->listPicklists->adminPage()->toArray());
    }

    public function store(SavePicklistOptionRequest $request, CreatePicklistOption $createOption): RedirectResponse
    {
        Gate::authorize('create', PicklistOption::class);

        $createOption->handle($request->toCreateCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option added.')]);

        return back();
    }

    public function update(SavePicklistOptionRequest $request, PicklistOption $picklistOption, UpdatePicklistOption $updateOption): RedirectResponse
    {
        Gate::authorize('update', $picklistOption);

        $updateOption->handle($picklistOption, $request->toUpdateCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option updated.')]);

        return back();
    }

    public function destroy(PicklistOption $picklistOption, DeletePicklistOption $deleteOption): RedirectResponse
    {
        Gate::authorize('delete', $picklistOption);

        $deleteOption->handle($picklistOption);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option deleted.')]);

        return back();
    }

    public function reorder(ReorderIdsRequest $request, ReorderPicklistOptions $reorderOptions): RedirectResponse
    {
        Gate::authorize('create', PicklistOption::class);

        $reorderOptions->handle($request->toCommand());

        return back();
    }
}
