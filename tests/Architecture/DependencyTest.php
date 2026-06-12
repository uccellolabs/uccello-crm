<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DependencyTest extends TestCase
{
    #[Test]
    public function deptrac_layer_rules_are_respected(): void
    {
        $process = new Process([base_path('vendor/bin/deptrac'), 'analyse', '--no-progress'], base_path());
        $process->run();

        $this->assertTrue(
            $process->isSuccessful(),
            "Deptrac violations:\n".$process->getOutput().$process->getErrorOutput(),
        );
    }

    #[Test]
    public function domain_layer_does_not_import_eloquent_or_http(): void
    {
        $output = shell_exec(
            'grep -rnE "use App\\\\Models\\\\|use Illuminate\\\\Database|use Illuminate\\\\Http" '
            .escapeshellarg(base_path('app/Domain')).' 2>/dev/null || true',
        );

        $this->assertSame('', trim((string) $output), "Domain layer must not import Eloquent/Http:\n{$output}");
    }

    #[Test]
    public function application_layer_does_not_import_infrastructure(): void
    {
        $output = shell_exec(
            'grep -rn "App\\\\Infrastructure" '
            .escapeshellarg(base_path('app/Application')).' 2>/dev/null || true',
        );

        $this->assertSame('', trim((string) $output), "Application layer must not import Infrastructure:\n{$output}");
    }

    #[Test]
    public function application_layer_does_not_use_eloquent_query_facade(): void
    {
        $output = shell_exec(
            'grep -rnE "DB::|::query\\(\\)|::create\\(" '
            .escapeshellarg(base_path('app/Application')).' 2>/dev/null || true',
        );

        $this->assertSame('', trim((string) $output), "Application layer must not use DB/ORM directly:\n{$output}");
    }

    #[Test]
    public function http_layer_does_not_import_infrastructure(): void
    {
        $output = shell_exec(
            'grep -rn "App\\\\Infrastructure" '
            .escapeshellarg(base_path('app/Http')).' 2>/dev/null || true',
        );

        $this->assertSame('', trim((string) $output), "Http layer must not import Infrastructure:\n{$output}");
    }

    #[Test]
    public function application_layer_does_not_query_eloquent_relations_directly(): void
    {
        $output = shell_exec(
            'grep -rnE "->activities\\(\\)|->tasks\\(\\)" '
            .escapeshellarg(base_path('app/Application')).' 2>/dev/null || true',
        );

        $this->assertSame('', trim((string) $output), "Application must not query Eloquent relations directly:\n{$output}");
    }
}
