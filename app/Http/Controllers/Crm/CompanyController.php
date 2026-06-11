<?php

namespace App\Http\Controllers\Crm;

use App\Application\Crm\Presenters\CrmRecordShowPresenter;
use App\Application\Crm\Services\CustomFields;
use App\Application\Crm\Services\Picklists;
use App\Application\Deals\Queries\GetDealStatsForCompanyQueryInterface;
use App\Concerns\InteractsWithCrmRecords;
use App\Domain\Shared\Enums\Picklist;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreCompanyRequest;
use App\Http\Requests\Crm\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    use InteractsWithCrmRecords;

    public function __construct(
        private readonly GetDealStatsForCompanyQueryInterface $dealStats,
        private readonly CrmRecordShowPresenter $showPresenter,
        private readonly CustomFields $customFields,
        private readonly Picklists $picklists,
    ) {}

    /**
     * Display a paginated, searchable list of companies.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Company::class);

        $search = trim((string) $request->string('search'));

        $companies = Company::query()
            ->with('owner:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'ilike', "%{$search}%")
                        ->orWhere('domain', 'ilike', "%{$search}%")
                        ->orWhere('city', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Company $company) => $this->toListItem($company));

        return Inertia::render('crm/companies/Index', [
            'companies' => $companies,
            'filters' => ['search' => $search],
            'can' => [
                'create' => $request->user()->can('create', Company::class),
            ],
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Company::class);

        return Inertia::render('crm/companies/Create', [
            'owners' => $this->teamMembers($request),
            'industries' => $this->picklists->options(Picklist::Industry),
            'customFields' => $this->customFields->forFrontend('company'),
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Gate::authorize('create', Company::class);

        $company = Company::create($request->validatedWithCustomFields());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company created.')]);

        return to_route('companies.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'company' => $company->id,
        ]);
    }

    /**
     * Display the specified company.
     */
    public function show(Request $request, Company $company): Response
    {
        Gate::authorize('view', $company);

        $company->load('owner:id,name');
        $sidebar = $this->showPresenter->sidebar($request->user(), $company, 'company', $company);

        return Inertia::render('crm/companies/Show', [
            'company' => $this->toDetail($company),
            'stats' => $this->dealStats->statsForCompany($company)->toArray(),
            'contacts' => $company->contacts()
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'email', 'job_title'])
                ->map(fn ($contact) => [
                    'id' => $contact->id,
                    'full_name' => $contact->full_name,
                    'email' => $contact->email,
                    'job_title' => $contact->job_title,
                ]),
            'deals' => array_map(
                fn ($summary) => $summary->toArray(),
                $this->showPresenter->dealsForRecord($company),
            ),
            ...$sidebar->toArray(),
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Request $request, Company $company): Response
    {
        Gate::authorize('update', $company);

        return Inertia::render('crm/companies/Edit', [
            'company' => $this->toDetail($company),
            'owners' => $this->teamMembers($request),
            'industries' => $this->picklists->options(Picklist::Industry),
            'customFields' => $this->customFields->forFrontend('company'),
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        Gate::authorize('update', $company);

        $company->update($request->validatedWithCustomFields());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company updated.')]);

        return to_route('companies.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'company' => $company->id,
        ]);
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Request $request, Company $company): RedirectResponse
    {
        Gate::authorize('delete', $company);

        $company->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company deleted.')]);

        return to_route('companies.index', [
            'current_team' => $request->user()->currentTeam->slug,
        ]);
    }

    /**
     * Transform a company into a list-row payload.
     *
     * @return array<string, mixed>
     */
    protected function toListItem(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'domain' => $company->domain,
            'industry' => $company->industry,
            'city' => $company->city,
            'phone' => $company->phone,
            'owner' => $company->owner ? ['id' => $company->owner->id, 'name' => $company->owner->name] : null,
        ];
    }

    /**
     * Transform a company into a full detail payload.
     *
     * @return array<string, mixed>
     */
    protected function toDetail(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'domain' => $company->domain,
            'industry' => $company->industry,
            'phone' => $company->phone,
            'website' => $company->website,
            'address' => $company->address,
            'city' => $company->city,
            'postal_code' => $company->postal_code,
            'country' => $company->country,
            'owner_id' => $company->owner_id,
            'owner' => $company->owner ? ['id' => $company->owner->id, 'name' => $company->owner->name] : null,
            'custom_fields' => $company->custom_fields ?? [],
            'created_at' => $company->created_at?->toISOString(),
        ];
    }
}
