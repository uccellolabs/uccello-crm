<?php

namespace App\Http\Requests\Crm;

use App\Application\Contacts\Commands\UpdateContactCommand;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateContactRequest extends CrmFormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'integer', $this->teamRecordRule('companies')],
            'owner_id' => ['nullable', 'integer', $this->teamMemberRule()],
            ...$this->customFieldRules(),
        ];
    }

    protected function customFieldEntity(): ?string
    {
        return 'contact';
    }

    public function toCommand(): UpdateContactCommand
    {
        $data = $this->validatedWithCustomFields();

        return new UpdateContactCommand(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            jobTitle: $data['job_title'] ?? null,
            companyId: isset($data['company_id']) ? (int) $data['company_id'] : null,
            ownerId: isset($data['owner_id']) ? (int) $data['owner_id'] : null,
            customFields: $data['custom_fields'] ?? null,
        );
    }
}
