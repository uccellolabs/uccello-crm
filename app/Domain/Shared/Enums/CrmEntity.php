<?php

namespace App\Domain\Shared\Enums;

/**
 * The CRM entities that support custom fields. The values match the
 * polymorphic morph aliases registered in AppServiceProvider.
 *
 * Pure domain enum: the mapping from an entity to its Eloquent model lives in
 * the infrastructure layer, not here.
 */
enum CrmEntity: string
{
    case Company = 'company';
    case Contact = 'contact';
    case Deal = 'deal';
    case Task = 'task';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Company => __('Companies'),
            self::Contact => __('Contacts'),
            self::Deal => __('Opportunities'),
            self::Task => __('Tasks'),
        };
    }

    /**
     * Entities that expose a custom-field form today. Tasks are managed inline
     * (quick-add) and have no dedicated form yet.
     *
     * @return array<int, self>
     */
    public static function withCustomFieldForms(): array
    {
        return [self::Company, self::Contact, self::Deal];
    }

    /**
     * Options for the admin entity tabs/selector (form-capable entities).
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $entity) => ['value' => $entity->value, 'label' => $entity->label()],
            self::withCustomFieldForms(),
        );
    }
}
