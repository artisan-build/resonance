<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ArtisanBuild\Resonance\Commands\ResonanceCommand;

class ResonanceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('resonance')
            ->hasConfigFile()
            ->hasCommand(ResonanceCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ResonanceManager::class);
        $this->app->alias(ResonanceManager::class, 'resonance');
    }
}
