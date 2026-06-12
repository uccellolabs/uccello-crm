<?php

namespace App\Http\Requests\Teams;

use App\Application\Teams\Commands\CreateTeamCommand;
use App\Application\Teams\Commands\UpdateTeamCommand;
use App\Rules\TeamName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new TeamName],
        ];
    }

    public function toCreateCommand(): CreateTeamCommand
    {
        return new CreateTeamCommand(name: $this->validated('name'));
    }

    public function toUpdateCommand(): UpdateTeamCommand
    {
        return new UpdateTeamCommand(name: $this->validated('name'));
    }
}
