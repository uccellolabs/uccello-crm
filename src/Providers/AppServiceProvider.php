<?php

namespace Uccello\Crm\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * App Service Provider
 */
class AppServiceProvider extends ServiceProvider
{
  /**
   * Indicates if loading of the provider is deferred.
   *
   * @var bool
   */
  protected $defer = false;

  public function boot()
  {
    // Views
    $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'crm');

    // Translations
    $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'crm');

    // Routes
    $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');

    // Publish assets
    $this->publishes([
      __DIR__ . '/../../public' => public_path('vendor/uccello/crm'),
    ], 'crm-assets');

    // Publish config
    $this->publishes([
      __DIR__.'/../../config/crm.php' => config_path('crm.php')
    ], 'crm-config');

    // Publish migrations
    $this->publishes([
      __DIR__.'/../../database/migrations/' => database_path('migrations')
    ], 'crm-migrations');
  }

  public function register()
  {
    // Config
    $this->mergeConfigFrom(
      __DIR__ . '/../../config/crm.php',
      'crm'
  );
  }
}