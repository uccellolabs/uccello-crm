<?php

namespace App\Actions\Fortify;

use App\Application\Auth\Commands\ResetPasswordCommand;
use App\Application\Auth\UseCases\ResetPassword;
use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(private ResetPassword $resetPassword) {}

    /**
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $this->resetPassword->handle($user, new ResetPasswordCommand(
            password: $input['password'],
        ));
    }
}
