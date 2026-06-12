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

    public function hasChoices(): bool
    {
        return in_array($this, [self::Select, self::MultiSelect], true);
    }

    public function isMultiple(): bool
    {
        return $this === self::MultiSelect;
    }
}
