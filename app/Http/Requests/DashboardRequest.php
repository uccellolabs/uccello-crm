<?php

namespace App\Http\Requests;

use App\Domain\Shared\ValueObjects\DateRange;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function dateRange(): DateRange
    {
        return DateRange::fromStrings(
            $this->string('from')->toString(),
            $this->string('to')->toString(),
        );
    }
}
