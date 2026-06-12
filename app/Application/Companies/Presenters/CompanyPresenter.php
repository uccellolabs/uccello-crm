<?php

namespace App\Application\Companies\Presenters;

use App\Models\Company;

class CompanyPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function toListItem(Company $company): array
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
     * @return array<string, mixed>
     */
    public function toDetail(Company $company): array
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
