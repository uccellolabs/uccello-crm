<?php

namespace App\Actions\Fortify;

use App\Application\Auth\Commands\RegisterUserCommand;
use App\Application\Auth\UseCases\RegisterUser;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private RegisterUser $registerUser) {}

    /**
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return $this->registerUser->handle(
            new RegisterUserCommand(
                name: $input['name'],
                email: $input['email'],
                password: $input['password'],
                locale: app()->getLocale(),
            ),
        );
    }
}
