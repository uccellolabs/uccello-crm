<?php

namespace App\Domain\Shared\Enums;

enum CustomFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Date = 'date';
    case Select = 'select';
    case MultiSelect = 'multiselect';
    case Checkbox = 'checkbox';
    case Email = 'email';
    case Url = 'url';
    case Phone = 'phone';
    case Relation = 'relation';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => __('Text'),
            self::Textarea => __('Long text'),
            self::Number => __('Number'),
            self::Date => __('Date'),
            self::Select => __('Dropdown'),
            self::MultiSelect => __('Multiple choice'),
            self::Checkbox => __('Checkbox'),
            self::Email => __('Email'),
            self::Url => __('Web link'),
            self::Phone => __('Phone'),
            self::Relation => __('Relation'),
        };
    }

    /**
     * Whether this type carries a list of choices in its options.
     */
    public function hasChoices(): bool
    {
        return in_array($this, [self::Select, self::MultiSelect], true);
    }

    /**
     * Whether this type stores multiple values.
     */
    public function isMultiple(): bool
    {
        return $this === self::MultiSelect;
    }

    /**
     * Options for the admin field-type select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }
}
