<?php

namespace Database\Factories;

use App\Domain\Shared\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CustomFieldDefinition>
 */
class CustomFieldDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->unique()->word().' '.fake()->word();

        return [
            'entity_type' => 'company',
            'key' => Str::slug($label, '_'),
            'label' => ucfirst($label),
            'type' => CustomFieldType::Text,
            'options' => null,
            'is_required' => false,
            'is_filterable' => false,
            'position' => 0,
            'help_text' => null,
        ];
    }

    /**
     * A select field with the given choices.
     *
     * @param  array<int, string>  $choices
     */
    public function select(array $choices): static
    {
        return $this->state(fn () => [
            'type' => CustomFieldType::Select,
            'options' => [
                'choices' => array_map(
                    fn (string $choice) => ['value' => $choice, 'label' => $choice],
                    $choices,
                ),
            ],
        ]);
    }
}
