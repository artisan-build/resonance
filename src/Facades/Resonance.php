<?php

namespace ArtisanBuild\Resonance\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ArtisanBuild\Resonance\Resonance
 */
class Resonance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ArtisanBuild\Resonance\Resonance::class;
    }
}
