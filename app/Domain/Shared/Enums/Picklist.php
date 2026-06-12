<?php

namespace App\Domain\Shared\Enums;

/**
 * The admin-configurable option lists ("picklists") of the CRM.
 */
enum Picklist: string
{
    case Industry = 'industry';
    case ActivityType = 'activity_type';
    case TaskPriority = 'task_priority';

    /**
     * @return list<array{value: string, label: string, color: string|null, is_system: bool}>
     */
    public function defaults(): array
    {
        return match ($this) {
            self::Industry => array_map(
                fn (string $name) => ['value' => $name, 'label' => $name, 'color' => null, 'is_system' => false],
                ['SaaS', 'Conseil', 'Industrie', 'Commerce', 'Santé',
                    'Finance', 'Immobilier', 'Éducation', 'Logistique', 'Média'],
            ),
            self::ActivityType => [
                ['value' => 'call', 'label' => 'Appel', 'color' => '#06b6d4', 'is_system' => true],
                ['value' => 'email', 'label' => 'E-mail', 'color' => '#2740e0', 'is_system' => true],
                ['value' => 'meeting', 'label' => 'Rendez-vous', 'color' => '#8b5cf6', 'is_system' => true],
                ['value' => 'note', 'label' => 'Note', 'color' => '#94a3b8', 'is_system' => true],
            ],
            self::TaskPriority => [
                ['value' => 'low', 'label' => 'Basse', 'color' => '#94a3b8', 'is_system' => true],
                ['value' => 'normal', 'label' => 'Normale', 'color' => '#2740e0', 'is_system' => true],
                ['value' => 'high', 'label' => 'Haute', 'color' => '#f43f5e', 'is_system' => true],
            ],
        };
    }
}
