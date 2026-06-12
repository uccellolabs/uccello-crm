<?php

namespace App\Domain\Teams\Services;

use App\Domain\Teams\Enums\TeamInvitationAcceptanceBlockReason;
use App\Models\TeamInvitation;

class TeamInvitationAcceptancePolicy
{
    public function canAccept(TeamInvitation $invitation, string $userEmail): bool
    {
        return $this->blockReason($invitation, $userEmail) === null;
    }

    public function blockReason(TeamInvitation $invitation, string $userEmail): ?TeamInvitationAcceptanceBlockReason
    {
        if ($invitation->isAccepted()) {
            return TeamInvitationAcceptanceBlockReason::AlreadyAccepted;
        }

        if ($invitation->isExpired()) {
            return TeamInvitationAcceptanceBlockReason::Expired;
        }

        if (strtolower($invitation->email) !== strtolower($userEmail)) {
            return TeamInvitationAcceptanceBlockReason::EmailMismatch;
        }

        return null;
    }
}
