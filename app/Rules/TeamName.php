<?php

namespace App\Rules;

use App\Domain\Teams\Services\TeamNamePolicy;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Routing\Route as RouteElement;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\PotentiallyTranslatedString;

class TeamName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (TeamNamePolicy::isReserved((string) $value, $this->routesPrefixes())) {
            $fail(__('This team name is reserved and cannot be used.'));
        }
    }

    /**
     * @return list<string>
     */
    protected function routesPrefixes(): array
    {
        return array_values(collect(Route::getRoutes()->getRoutes())
            ->map(fn (RouteElement $route) => $route->uri)
            ->map(fn (string $uri) => explode('/', $uri)[0])
            ->reject(fn (string $uri) => str_contains($uri, '{'))
            ->filter(fn (string $uri) => $uri !== '')
            ->unique()
            ->sort()
            ->values()
            ->toArray());
    }
}
