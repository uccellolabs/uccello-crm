<?php

namespace App\Http\Requests\Settings;

use App\Application\Settings\Commands\UpdateLocaleCommand;
use App\Http\Middleware\SetLocale;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::in(SetLocale::SUPPORTED)],
        ];
    }

    public function toCommand(): UpdateLocaleCommand
    {
        return new UpdateLocaleCommand(
            locale: $this->validated('locale'),
        );
    }
}
