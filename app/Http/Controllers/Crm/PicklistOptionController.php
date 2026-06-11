<?php

namespace App\Http\Controllers\Crm;

use App\Application\Admin\UseCases\ReorderByIds;
use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use App\Domain\Shared\ValueObjects\UniqueSlug;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\SavePicklistOptionRequest;
use App\Models\PicklistOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PicklistOptionController extends Controller
{
    public function __construct(
        private readonly Picklists $picklists,
        private readonly ReorderByIds $reorderByIds,
    ) {}

    /**
     * Display the picklist administration screen.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', PicklistOption::class);

        $options = collect(Picklist::cases())
            ->mapWithKeys(fn (Picklist $list) => [
                $list->value => $this->picklists->rows($list)
                    ->map(fn (PicklistOption $option) => [
                        'id' => $option->id,
                        'value' => $option->value,
                        'label' => $option->label,
                        'color' => $option->color,
                        'position' => $option->position,
                        'is_system' => $option->is_system,
                    ])
                    ->values()
                    ->all(),
            ]);

        return Inertia::render('crm/picklists/Index', [
            'options' => $options,
            'picklists' => Picklist::options(),
        ]);
    }

    /**
     * Add an option to a picklist.
     */
    public function store(SavePicklistOptionRequest $request): RedirectResponse
    {
        Gate::authorize('create', PicklistOption::class);

        $picklist = Picklist::from($request->validated('picklist'));
        $label = $request->validated('label');

        $slug = UniqueSlug::generate(
            $label,
            fn (string $value) => PicklistOption::query()
                ->where('picklist', $picklist->value)
                ->where('value', $value)
                ->exists(),
        );

        PicklistOption::create([
            'picklist' => $picklist->value,
            'value' => $slug->value,
            'label' => $label,
            'color' => $request->validated('color'),
            'position' => $this->nextPosition($picklist),
            'is_system' => false,
        ]);

        $this->picklists->flush();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option added.')]);

        return back();
    }

    /**
     * Rename or recolor an option. The stored value is immutable so existing
     * records keep pointing to it.
     */
    public function update(SavePicklistOptionRequest $request, PicklistOption $picklistOption): RedirectResponse
    {
        Gate::authorize('update', $picklistOption);

        $picklistOption->update([
            'label' => $request->validated('label'),
            'color' => $request->validated('color'),
        ]);

        $this->picklists->flush();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option updated.')]);

        return back();
    }

    /**
     * Delete a non-system option. Records already storing the value keep it;
     * they render the raw value as a fallback label.
     */
    public function destroy(PicklistOption $picklistOption): RedirectResponse
    {
        Gate::authorize('delete', $picklistOption);

        $picklistOption->delete();

        $this->picklists->flush();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Option deleted.')]);

        return back();
    }

    /**
     * Persist a new ordering of options within a picklist.
     */
    public function reorder(Request $request): RedirectResponse
    {
        Gate::authorize('create', PicklistOption::class);

        $ids = array_values($request->collect('ids')->map(fn ($id) => (int) $id)->all());

        $this->reorderByIds->handle(PicklistOption::class, $ids);

        $this->picklists->flush();

        return back();
    }

    /**
     * The next position within a picklist.
     */
    protected function nextPosition(Picklist $picklist): int
    {
        return (int) PicklistOption::query()->forList($picklist->value)->max('position') + 1;
    }
}
