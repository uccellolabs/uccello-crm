<?php

namespace App\Http\Controllers\Crm;

use App\Application\Contacts\Queries\GetContactShowPageQueryInterface;
use App\Application\Contacts\Queries\ListContactsQueryInterface;
use App\Application\Contacts\UseCases\CreateContact;
use App\Application\Contacts\UseCases\DeleteContact;
use App\Application\Contacts\UseCases\UpdateContact;
use App\Application\Crm\Queries\GetCrmRecordFormDataQueryInterface;
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
    public function __construct(
        private readonly ListContactsQueryInterface $listContacts,
        private readonly GetContactShowPageQueryInterface $contactShowPage,
        private readonly GetCrmRecordFormDataQueryInterface $formData,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Contact::class);

        $search = trim((string) $request->string('search'));

        return Inertia::render('crm/contacts/Index', $this->listContacts->paginate($request->user(), $search)->toArray());
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Contact::class);

        return Inertia::render('crm/contacts/Create', $this->formData->forContactCreate(
            $request->user(),
            $request->integer('company_id') ?: null,
        ));
    }

    public function store(StoreContactRequest $request, CreateContact $createContact): RedirectResponse
    {
        Gate::authorize('create', Contact::class);

        $contact = $createContact->handle($request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact created.')]);

        return to_route('contacts.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'contact' => $contact->id,
        ]);
    }

    public function show(Request $request, Contact $contact): Response
    {
        Gate::authorize('view', $contact);

        return Inertia::render('crm/contacts/Show', $this->contactShowPage->forContact($request->user(), $contact)->toArray());
    }

    public function edit(Request $request, Contact $contact): Response
    {
        Gate::authorize('update', $contact);

        return Inertia::render('crm/contacts/Edit', $this->formData->forContactEdit($request->user(), $contact));
    }

    public function update(UpdateContactRequest $request, Contact $contact, UpdateContact $updateContact): RedirectResponse
    {
        Gate::authorize('update', $contact);

        $updateContact->handle($contact, $request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact updated.')]);

        return to_route('contacts.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'contact' => $contact->id,
        ]);
    }

    public function destroy(Request $request, Contact $contact, DeleteContact $deleteContact): RedirectResponse
    {
        Gate::authorize('delete', $contact);

        $deleteContact->handle($contact);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Contact deleted.')]);

        return to_route('contacts.index', [
            'current_team' => $request->user()->currentTeam->slug,
        ]);
    }
}
