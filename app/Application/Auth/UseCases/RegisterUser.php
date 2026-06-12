<?php

namespace App\Application\Auth\UseCases;

use App\Application\Auth\Commands\RegisterUserCommand;
use App\Application\Teams\UseCases\CreateTeam;
use App\Application\Teams\Commands\CreateTeamCommand;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Models\User;

class RegisterUser
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly CreateTeam $createTeam,
    ) {}

    public function handle(RegisterUserCommand $command): User
    {
        $user = $this->users->create(
            $command->name,
            $command->email,
            $command->password,
            $command->locale,
        );

        $this->createTeam->handle($user, new CreateTeamCommand(
            name: $user->name."'s Team",
            isPersonal: true,
        ));

        return $user;
    }
}
