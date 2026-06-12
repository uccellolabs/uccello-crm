<?php

namespace App\Http\Requests\Crm;

use App\Application\Companies\Commands\CreateCompanyCommand;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreCompanyRequest extends CrmFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', 'integer', $this->teamMemberRule()],
            ...$this->customFieldRules(),
        ];
    }

    protected function customFieldEntity(): ?string
    {
        return 'company';
    }

    public function toCommand(): CreateCompanyCommand
    {
        $data = $this->validatedWithCustomFields();

        return new CreateCompanyCommand(
            name: $data['name'],
            domain: $data['domain'] ?? null,
            industry: $data['industry'] ?? null,
            phone: $data['phone'] ?? null,
            website: $data['website'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            country: $data['country'] ?? null,
            ownerId: isset($data['owner_id']) ? (int) $data['owner_id'] : null,
            customFields: $data['custom_fields'] ?? null,
        );
    }
}
