<?php

namespace App\Http\Controllers\Crm;

use App\Application\Companies\Queries\GetCompanyShowPageQueryInterface;
use App\Application\Companies\Queries\ListCompaniesQueryInterface;
use App\Application\Companies\UseCases\CreateCompany;
use App\Application\Companies\UseCases\DeleteCompany;
use App\Application\Companies\UseCases\UpdateCompany;
use App\Application\Crm\Queries\GetCrmRecordFormDataQueryInterface;
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
    public function __construct(
        private readonly ListCompaniesQueryInterface $listCompanies,
        private readonly GetCompanyShowPageQueryInterface $companyShowPage,
        private readonly GetCrmRecordFormDataQueryInterface $formData,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Company::class);

        $search = trim((string) $request->string('search'));

        return Inertia::render('crm/companies/Index', $this->listCompanies->paginate($request->user(), $search)->toArray());
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Company::class);

        return Inertia::render('crm/companies/Create', $this->formData->forCompanyCreate($request->user()));
    }

    public function store(StoreCompanyRequest $request, CreateCompany $createCompany): RedirectResponse
    {
        Gate::authorize('create', Company::class);

        $company = $createCompany->handle($request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company created.')]);

        return to_route('companies.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'company' => $company->id,
        ]);
    }

    public function show(Request $request, Company $company): Response
    {
        Gate::authorize('view', $company);

        return Inertia::render('crm/companies/Show', $this->companyShowPage->forCompany($request->user(), $company)->toArray());
    }

    public function edit(Request $request, Company $company): Response
    {
        Gate::authorize('update', $company);

        return Inertia::render('crm/companies/Edit', $this->formData->forCompanyEdit($request->user(), $company));
    }

    public function update(UpdateCompanyRequest $request, Company $company, UpdateCompany $updateCompany): RedirectResponse
    {
        Gate::authorize('update', $company);

        $updateCompany->handle($company, $request->toCommand());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company updated.')]);

        return to_route('companies.show', [
            'current_team' => $request->user()->currentTeam->slug,
            'company' => $company->id,
        ]);
    }

    public function destroy(Request $request, Company $company, DeleteCompany $deleteCompany): RedirectResponse
    {
        Gate::authorize('delete', $company);

        $deleteCompany->handle($company);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company deleted.')]);

        return to_route('companies.index', [
            'current_team' => $request->user()->currentTeam->slug,
        ]);
    }
}
