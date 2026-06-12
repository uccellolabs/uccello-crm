<?php

namespace App\Application\Crm\Queries;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;

interface GetCrmRecordFormDataQueryInterface
{
    /** @return array<string, mixed> */
    public function forCompanyCreate(User $user): array;

    /** @return array<string, mixed> */
    public function forCompanyEdit(User $user, Company $company): array;

    /** @return array<string, mixed> */
    public function forContactCreate(User $user, ?int $companyId): array;

    /** @return array<string, mixed> */
    public function forContactEdit(User $user, Contact $contact): array;

    /** @return array<string, mixed> */
    public function forDealCreate(User $user, ?int $stageId, ?int $companyId, ?int $contactId): array;

    /** @return array<string, mixed> */
    public function forDealEdit(User $user, Deal $deal): array;

    /** @return array<string, mixed> */
    public function forTaskCreate(User $user): array;
}
