<?php

namespace App\Rules;

use App\Domain\Teams\Services\TeamInvitationAcceptancePolicy;
use App\Models\TeamInvitation;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidTeamInvitation implements ValidationRule
{
    public function __construct(
        protected ?User $user,
        protected ?TeamInvitationAcceptancePolicy $policy = null,
    ) {
        $this->policy ??= new TeamInvitationAcceptancePolicy;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof TeamInvitation || ! $this->user instanceof User) {
            $fail(__('This invitation was sent to a different email address.'));

            return;
        }

        $reason = $this->policy->blockReason($value, $this->user->email);

        if ($reason === null) {
            return;
        }

        $fail(match ($reason->value) {
            'already_accepted' => __('This invitation has already been accepted.'),
            'expired' => __('This invitation has expired.'),
            default => __('This invitation was sent to a different email address.'),
        });
    }
}
