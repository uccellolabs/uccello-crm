<?php

namespace App\Application\Contacts\Presenters;

use App\Models\Contact;

class ContactPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function toListItem(Contact $contact): array
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
     * @return array<string, mixed>
     */
    public function toDetail(Contact $contact): array
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
