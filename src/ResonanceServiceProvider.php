<?php

namespace ArtisanBuild\Resonance;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ArtisanBuild\Resonance\Commands\ResonanceCommand;

class ResonanceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('resonance')
            ->hasConfigFile()
            ->hasCommand(ResonanceCommand::class);
    }

    // From PoC
    // public function boot(): void
    // {
    //     $this->publishes([
    //         __DIR__.'/../config/resonance.php' => config_path('resonance.php'),
    //     ], 'config');
    // }

    // public function register(): void
    // {
    //     $this->mergeConfigFrom(__DIR__.'/../config/resonance.php', 'resonance');
    // }
}
