<?php

use App\Infrastructure\InfrastructureServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    InfrastructureServiceProvider::class,
    FortifyServiceProvider::class,
];
