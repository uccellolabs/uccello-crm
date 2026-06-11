<?php

namespace Tests\Unit\Domain;

use App\Domain\Shared\ValueObjects\UniqueSlug;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UniqueSlugTest extends TestCase
{
    #[Test]
    public function it_generates_a_base_slug_when_available(): void
    {
        $slug = UniqueSlug::generate('My Option', fn () => false);

        $this->assertSame('my_option', $slug->value);
    }

    #[Test]
    public function it_appends_suffix_when_slug_exists(): void
    {
        $existing = ['my_option'];

        $slug = UniqueSlug::generate('My Option', function (string $value) use (&$existing) {
            return in_array($value, $existing, true);
        });

        $this->assertSame('my_option_2', $slug->value);
    }
}
