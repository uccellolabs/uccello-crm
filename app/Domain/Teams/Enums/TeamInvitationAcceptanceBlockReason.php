<?php

namespace App\Domain\Teams\Enums;

enum TeamInvitationAcceptanceBlockReason: string
{
    case AlreadyAccepted = 'already_accepted';
    case Expired = 'expired';
    case EmailMismatch = 'email_mismatch';
}
