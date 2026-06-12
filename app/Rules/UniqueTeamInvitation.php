<?php

namespace App\Rules;

use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\Team;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueTeamInvitation implements ValidationRule
{
    public function __construct(
        protected Team $team,
        protected ?TeamRepositoryInterface $teams = null,
    ) {
        $this->teams ??= app(TeamRepositoryInterface::class);
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower((string) $value);

        if ($this->teams->emailIsMember($this->team, $email)) {
            $fail(__('This user is already a member of the team.'));

            return;
        }

        if ($this->teams->hasPendingInvitationForEmail($this->team, $email)) {
            $fail(__('An invitation has already been sent to this email address.'));
        }
    }
}
