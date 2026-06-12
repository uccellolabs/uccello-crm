<?php

namespace Tests\Unit\Domain\Teams;

use App\Domain\Teams\Enums\TeamInvitationAcceptanceBlockReason;
use App\Domain\Teams\Services\TeamInvitationAcceptancePolicy;
use App\Models\TeamInvitation;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamInvitationAcceptancePolicyTest extends TestCase
{
    private TeamInvitationAcceptancePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TeamInvitationAcceptancePolicy;
    }

    #[Test]
    public function it_allows_a_pending_invitation_for_the_matching_email(): void
    {
        $invitation = new TeamInvitation([
            'email' => 'Member@Example.com',
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($this->policy->canAccept($invitation, 'member@example.com'));
        $this->assertNull($this->policy->blockReason($invitation, 'member@example.com'));
    }

    #[Test]
    public function it_blocks_accepted_invitations(): void
    {
        $invitation = new TeamInvitation([
            'email' => 'member@example.com',
            'accepted_at' => now(),
        ]);

        $this->assertFalse($this->policy->canAccept($invitation, 'member@example.com'));
        $this->assertSame(
            TeamInvitationAcceptanceBlockReason::AlreadyAccepted,
            $this->policy->blockReason($invitation, 'member@example.com'),
        );
    }

    #[Test]
    public function it_blocks_expired_invitations(): void
    {
        $invitation = new TeamInvitation([
            'email' => 'member@example.com',
            'expires_at' => Carbon::parse('2020-01-01'),
        ]);

        $this->assertFalse($this->policy->canAccept($invitation, 'member@example.com'));
        $this->assertSame(
            TeamInvitationAcceptanceBlockReason::Expired,
            $this->policy->blockReason($invitation, 'member@example.com'),
        );
    }

    #[Test]
    public function it_blocks_email_mismatches(): void
    {
        $invitation = new TeamInvitation([
            'email' => 'member@example.com',
            'expires_at' => now()->addDay(),
        ]);

        $this->assertFalse($this->policy->canAccept($invitation, 'other@example.com'));
        $this->assertSame(
            TeamInvitationAcceptanceBlockReason::EmailMismatch,
            $this->policy->blockReason($invitation, 'other@example.com'),
        );
    }
}
