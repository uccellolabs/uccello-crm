<?php

namespace App\Domain\Shared\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    /**
     * @return array<TeamPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => TeamPermission::cases(),
            self::Admin => [
                TeamPermission::UpdateTeam,
                TeamPermission::CreateInvitation,
                TeamPermission::CancelInvitation,
                TeamPermission::ViewCrm,
                TeamPermission::ManageCrmRecords,
                TeamPermission::ManageCustomFields,
            ],
            self::Member => [
                TeamPermission::ViewCrm,
                TeamPermission::ManageCrmRecords,
            ],
        };
    }

    public function hasPermission(TeamPermission $permission): bool
    {
        return in_array($permission, $this->permissions());
    }

    public function level(): int
    {
        return match ($this) {
            self::Owner => 3,
            self::Admin => 2,
            self::Member => 1,
        };
    }

    public function isAtLeast(TeamRole $role): bool
    {
        return $this->level() >= $role->level();
    }

    /**
     * @return list<TeamRole>
     */
    public static function assignable(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (TeamRole $role) => $role !== self::Owner,
        ));
    }

    /**
     * @return list<string>
     */
    public static function assignableValues(): array
    {
        return array_map(fn (TeamRole $role) => $role->value, self::assignable());
    }
}
