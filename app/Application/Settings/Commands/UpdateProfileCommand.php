<?php

namespace App\Application\Settings\Commands;

final readonly class UpdateProfileCommand
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
