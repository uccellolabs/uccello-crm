<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Presenters\CrmRecordShowPresenter;
use App\Application\Crm\Services\CrmFormOptions;
use App\Application\Crm\Services\CustomFields;
use App\Application\Deals\Queries\GetDealStatsForContactQueryInterface;
use App\Concerns\InteractsWithCrmRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreContactRequest;
use App\Http\Requests\Crm\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    use InteractsWithCrmRecords;

    public function __construct(
        private readonly GetDealStatsForContactQueryInterface $dealStats,
        private readonly CrmRecordShowPresenter $showPresenter,
        private readonly CrmFormOptions $formOptions,
        private readonly CustomFields $customFields,
    ) {}

    /**
     * Display a paginated, searchable list of contacts.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Contact::class);

        $search = trim((string) $request->string('search'));

        $contacts = Contact::query()
            ->with(['company:id,name', 'owner:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Contact $contact) => $this->toListItem($contact));

        return Inertia::render('crm/contacts/Index', [
            'contacts' => $contacts,
            'filters' => ['search' => $search],
            'can' => ['create' => $request->user()->can('create', Contact::class)],
        ]);
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Contact::class);

        return Inertia::render('crm/contacts/Create', [
            'owners' => $this->teamMembers($request),
            'companies' => array_map(
                fn ($option) => $option->toArray(),
                $this->formOptions->companies(),
            ),
            'companyId' => $request->integer('company_id') ?: null,
            'customFields' => $this->customFields->forFrontend('contact'),
        ]);
    }

    /**
     * Store a newly created contact.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        Gate::authorize('create', Contact::class);

        $contact = Contact::create($request->validatedWithCustomFields());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact created.')]);

        return to_route('contacts.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'contact' => $contact->id,
        ]);
    }

    /**
     * Display the specified contact with its timeline and tasks.
     */
    public function show(Request $request, Contact $contact): Response
    {
        Gate::authorize('view', $contact);

        $contact->load(['company:id,name', 'owner:id,name']);
        $sidebar = $this->showPresenter->sidebar($request->user(), $contact, 'contact', $contact);

        return Inertia::render('crm/contacts/Show', [
            'contact' => $this->toDetail($contact),
            'stats' => $this->dealStats->statsForContact($contact)->toArray(),
            'deals' => array_map(
                fn ($summary) => $summary->toArray(),
                $this->showPresenter->dealsForRecord($contact),
            ),
            ...$sidebar->toArray(),
        ]);
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Request $request, Contact $contact): Response
    {
        Gate::authorize('update', $contact);

        return Inertia::render('crm/contacts/Edit', [
            'contact' => $this->toDetail($contact),
            'owners' => $this->teamMembers($request),
            'companies' => array_map(
                fn ($option) => $option->toArray(),
                $this->formOptions->companies(),
            ),
            'customFields' => $this->customFields->forFrontend('contact'),
        ]);
    }

    /**
     * Update the specified contact.
     */
    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        Gate::authorize('update', $contact);

        $contact->update($request->validatedWithCustomFields());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact updated.')]);

        return to_route('contacts.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'contact' => $contact->id,
        ]);
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Request $request, Contact $contact): RedirectResponse
    {
        Gate::authorize('delete', $contact);

        $contact->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact deleted.')]);

        return to_route('contacts.index', [
            'current_team' => $request->user()->currentTeam->slug,
        ]);
    }

    /**
     * Transform a contact into a list-row payload.
     *
     * @return array<string, mixed>
     */
    protected function toListItem(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'full_name' => $contact->full_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'job_title' => $contact->job_title,
            'company' => $contact->company ? ['id' => $contact->company->id, 'name' => $contact->company->name] : null,
            'owner' => $contact->owner ? ['id' => $contact->owner->id, 'name' => $contact->owner->name] : null,
        ];
    }

    /**
     * Transform a contact into a full detail payload.
     *
     * @return array<string, mixed>
     */
    protected function toDetail(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'full_name' => $contact->full_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'job_title' => $contact->job_title,
            'company_id' => $contact->company_id,
            'company' => $contact->company ? ['id' => $contact->company->id, 'name' => $contact->company->name] : null,
            'owner_id' => $contact->owner_id,
            'owner' => $contact->owner ? ['id' => $contact->owner->id, 'name' => $contact->owner->name] : null,
            'custom_fields' => $contact->custom_fields ?? [],
            'created_at' => $contact->created_at?->toISOString(),
        ];
    }
}
