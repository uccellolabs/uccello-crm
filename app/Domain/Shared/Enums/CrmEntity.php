<?php

namespace App\Domain\Shared\Enums;

/**
 * The CRM entities that support custom fields. The values match the
 * polymorphic morph aliases registered in AppServiceProvider.
 */
enum CrmEntity: string
{
    case Company = 'company';
    case Contact = 'contact';
    case Deal = 'deal';
    case Task = 'task';

    /**
     * @return array<int, self>
     */
    public static function withCustomFieldForms(): array
    {
        return [self::Company, self::Contact, self::Deal];
    }
}
