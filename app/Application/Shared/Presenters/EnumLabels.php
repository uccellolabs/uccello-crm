<?php

namespace App\Application\Shared\Presenters;

use App\Domain\Shared\Enums\CrmEntity;
use App\Domain\Shared\Enums\CustomFieldType;
use App\Domain\Shared\Enums\DealStatus;
use App\Domain\Shared\Enums\Picklist;
use App\Domain\Shared\Enums\TeamRole;

final class EnumLabels
{
    public static function teamRole(TeamRole $role): string
    {
        return match ($role) {
            TeamRole::Owner => __('Owner'),
            TeamRole::Admin => __('Admin'),
            TeamRole::Member => __('Member'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function assignableTeamRoles(): array
    {
        return array_map(
            fn (TeamRole $role) => ['value' => $role->value, 'label' => self::teamRole($role)],
            TeamRole::assignable(),
        );
    }

    public static function dealStatus(DealStatus $status): string
    {
        return match ($status) {
            DealStatus::Open => __('Open'),
            DealStatus::Won => __('Won'),
            DealStatus::Lost => __('Lost'),
        };
    }

    public static function picklist(Picklist $picklist): string
    {
        return match ($picklist) {
            Picklist::Industry => __('Industries'),
            Picklist::ActivityType => __('Activity types'),
            Picklist::TaskPriority => __('Task priorities'),
        };
    }

    public static function picklistDescription(Picklist $picklist): string
    {
        return match ($picklist) {
            Picklist::Industry => __('Offered in the company « Industry » field.'),
            Picklist::ActivityType => __('Offered when logging an activity.'),
            Picklist::TaskPriority => __('Offered when creating a task.'),
        };
    }

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    public static function picklistOptions(): array
    {
        return array_map(
            fn (Picklist $list) => [
                'value' => $list->value,
                'label' => self::picklist($list),
                'description' => self::picklistDescription($list),
            ],
            Picklist::cases(),
        );
    }

    public static function customFieldType(CustomFieldType $type): string
    {
        return match ($type) {
            CustomFieldType::Text => __('Text'),
            CustomFieldType::Textarea => __('Long text'),
            CustomFieldType::Number => __('Number'),
            CustomFieldType::Date => __('Date'),
            CustomFieldType::Select => __('Dropdown'),
            CustomFieldType::MultiSelect => __('Multiple choice'),
            CustomFieldType::Checkbox => __('Checkbox'),
            CustomFieldType::Email => __('Email'),
            CustomFieldType::Url => __('Web link'),
            CustomFieldType::Phone => __('Phone'),
            CustomFieldType::Relation => __('Relation'),
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function customFieldTypeOptions(): array
    {
        return array_map(
            fn (CustomFieldType $type) => ['value' => $type->value, 'label' => self::customFieldType($type)],
            CustomFieldType::cases(),
        );
    }

    public static function crmEntity(CrmEntity $entity): string
    {
        return match ($entity) {
            CrmEntity::Company => __('Companies'),
            CrmEntity::Contact => __('Contacts'),
            CrmEntity::Deal => __('Opportunities'),
            CrmEntity::Task => __('Tasks'),
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function crmEntityOptions(): array
    {
        return array_map(
            fn (CrmEntity $entity) => ['value' => $entity->value, 'label' => self::crmEntity($entity)],
            CrmEntity::withCustomFieldForms(),
        );
    }
}
