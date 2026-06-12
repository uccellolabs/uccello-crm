<?php

namespace App\Http\Requests\Settings;

use App\Application\Settings\Commands\UpdateProfileCommand;
use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules($this->user()->id);
    }

    public function toCommand(): UpdateProfileCommand
    {
        $data = $this->validated();

        return new UpdateProfileCommand(
            name: $data['name'],
            email: $data['email'],
        );
    }
}
