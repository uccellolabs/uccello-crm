<?php

namespace App\Http\Requests\Teams;

use App\Application\Teams\Commands\UpdateMemberCommand;
use App\Domain\Shared\Enums\TeamRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(TeamRole::assignableValues())],
        ];
    }

    public function toCommand(): UpdateMemberCommand
    {
        return new UpdateMemberCommand(
            role: TeamRole::from($this->validated('role')),
        );
    }
}
