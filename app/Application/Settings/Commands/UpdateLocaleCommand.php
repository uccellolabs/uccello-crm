<?php

namespace App\Application\Settings\Commands;

final readonly class UpdateLocaleCommand
{
    public function __construct(
        public string $locale,
    ) {}
}
