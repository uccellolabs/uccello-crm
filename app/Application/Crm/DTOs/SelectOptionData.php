<?php

namespace App\Application\Crm\DTOs;

final readonly class SelectOptionData
{
    public function __construct(
        public int|string $value,
        public string $label,
    ) {}

    /**
     * @return array{value: int|string, label: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
        ];
    }

    /**
     * @param  iterable<int|string, string>  $options
     * @return list<self>
     */
    public static function fromIterable(iterable $options): array
    {
        $result = [];

        foreach ($options as $value => $label) {
            $result[] = new self($value, (string) $label);
        }

        return $result;
    }
}
