<?php

namespace App\Application\Pipelines\Queries;

use App\Application\Pipelines\DTOs\PipelineSettingsData;

interface GetPipelineSettingsQueryInterface
{
    public function all(): PipelineSettingsData;
}
