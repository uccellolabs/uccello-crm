<?php

namespace App\Domain\Shared\Enums;

enum TeamPermission: string
{
    case UpdateTeam = 'team:update';
    case DeleteTeam = 'team:delete';

    case AddMember = 'member:add';
    case UpdateMember = 'member:update';
    case RemoveMember = 'member:remove';

    case CreateInvitation = 'invitation:create';
    case CancelInvitation = 'invitation:cancel';

    case ViewCrm = 'crm:view';
    case ManageCrmRecords = 'crm:manage';
    case ManageCustomFields = 'custom_fields:manage';
}
