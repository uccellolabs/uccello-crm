<?php

namespace App\Http\Controllers\Crm;

use App\Application\CustomFields\Queries\ListCustomFieldsQueryInterface;
use App\Application\CustomFields\UseCases\CreateCustomField;
use App\Application\CustomFields\UseCases\DeleteCustomField;
use App\Application\CustomFields\UseCases\ReorderCustomFields;
use App\Application\CustomFields\UseCases\UpdateCustomField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\SaveCustomFieldRequest;
use App\Http\Requests\ReorderIdsRequest;
use App\Models\CustomFieldDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CustomFieldDefinitionController extends Controller
{
    public function __construct(
        private readonly ListCustomFieldsQueryInterface $listCustomFields,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', CustomFieldDefinition::class);

        return Inertia::render('crm/custom-fields/Index', $this->listCustomFields->adminPage()->toArray());
    }

    public function store(SaveCustomFieldRequest $request, CreateCustomField $createCustomField): RedirectResponse
    {
        Gate::authorize('create', CustomFieldDefinition::class);

        $createCustomField->handle($request->toCreateCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom field created.')]);

        return back();
    }

    public function update(SaveCustomFieldRequest $request, CustomFieldDefinition $customField, UpdateCustomField $updateCustomField): RedirectResponse
    {
        Gate::authorize('update', $customField);

        $updateCustomField->handle($customField, $request->toUpdateCommand($customField));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom field updated.')]);

        return back();
    }

    public function destroy(CustomFieldDefinition $customField, DeleteCustomField $deleteCustomField): RedirectResponse
    {
        Gate::authorize('delete', $customField);

        $deleteCustomField->handle($customField);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Custom field deleted.')]);

        return back();
    }

    public function reorder(ReorderIdsRequest $request, ReorderCustomFields $reorderCustomFields): RedirectResponse
    {
        Gate::authorize('create', CustomFieldDefinition::class);

        $reorderCustomFields->handle($request->toCommand());

        return back();
    }
}
