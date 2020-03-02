<?php

namespace Rockbuzz\LaraPricing;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    public function boot(Filesystem $filesystem)
    {
        $projectPath = database_path('migrations') . DIRECTORY_SEPARATOR;
        $localPath = __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' .
            DIRECTORY_SEPARATOR;

        if (! $this->hasMigrationInProject($projectPath, $filesystem)) {
            $this->loadMigrationsFrom($localPath . '2020_03_01_000000_create_pricing_tables.php');

            $this->publishes([
                $localPath . '2020_03_01_000000_create_pricing_tables.php' =>
                    $projectPath . now()->format('Y_m_d_his') . '_create_pricing_tables.php'
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . '/config/pricing.php' => config_path('pricing.php')
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/pricing.php', 'pricing');
    }

    private function hasMigrationInProject(string $path, Filesystem $filesystem)
    {
        return count($filesystem->glob($path . '*_create_pricing_tables.php')) > 0;
    }
}
