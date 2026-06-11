<?php

namespace App\Http\Requests\Crm;

class UpdateCompanyRequest extends StoreCompanyRequest
{
    // Same rules as creating a company. Kept as a distinct request so update
    // validation can diverge (and merge custom-field rules) independently.
}
