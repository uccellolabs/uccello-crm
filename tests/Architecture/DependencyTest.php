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
}
