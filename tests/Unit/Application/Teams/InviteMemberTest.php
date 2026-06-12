<?php

namespace Tests\Unit\Application\Teams;

use App\Application\Teams\Commands\InviteMemberCommand;
use App\Application\Teams\UseCases\InviteMember;
use App\Domain\Shared\Enums\TeamRole;
use App\Domain\Teams\Repositories\TeamRepositoryInterface;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InviteMemberTest extends TestCase
{
    #[Test]
    public function it_creates_an_invitation_through_the_repository(): void
    {
        $team = new Team(['name' => 'Acme']);
        $team->id = 4;

        $inviter = new User(['name' => 'Owner', 'email' => 'owner@example.com']);
        $inviter->id = 9;

        $invitation = new TeamInvitation(['email' => 'new@example.com']);
        $command = new InviteMemberCommand(email: 'new@example.com', role: TeamRole::Admin);

        $teams = $this->createMock(TeamRepositoryInterface::class);
        $teams->expects($this->once())->method('createInvitation')->with(
            $team,
            'new@example.com',
            TeamRole::Admin,
            9,
            $this->isInstanceOf(DateTimeInterface::class),
        )->willReturn($invitation);

        $result = (new InviteMember($teams))->handle($team, $inviter, $command);

        $this->assertSame($invitation, $result);
    }

    #[Test]
    public function it_passes_member_role_to_the_repository(): void
    {
        $team = new Team;
        $inviter = new User;
        $inviter->id = 2;

        $teams = $this->createMock(TeamRepositoryInterface::class);
        $teams->expects($this->once())->method('createInvitation')->with(
            $team,
            'member@example.com',
            TeamRole::Member,
            2,
            $this->anything(),
        )->willReturn(new TeamInvitation);

        (new InviteMember($teams))->handle(
            $team,
            $inviter,
            new InviteMemberCommand(email: 'member@example.com', role: TeamRole::Member),
        );
    }

    #[Test]
    public function it_uses_the_inviter_id_when_creating_the_invitation(): void
    {
        $team = new Team;
        $inviter = new User;
        $inviter->id = 42;

        $teams = $this->createMock(TeamRepositoryInterface::class);
        $teams->expects($this->once())->method('createInvitation')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            42,
            $this->anything(),
        )->willReturn(new TeamInvitation);

        (new InviteMember($teams))->handle(
            $team,
            $inviter,
            new InviteMemberCommand(email: 'x@example.com', role: TeamRole::Member),
        );
    }
}
