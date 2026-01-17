<?php

declare(strict_types=1);

namespace ArtisanBuild\Resonance\Facades;

use ArtisanBuild\Resonance\Channel\Channel;
use ArtisanBuild\Resonance\Channel\PresenceChannel;
use ArtisanBuild\Resonance\ResonanceManager;
use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ArtisanBuild\Resonance\Resonance connection(?string $name = null)
 * @method static \ArtisanBuild\Resonance\Resonance driver(?string $driver = null)
 * @method static Channel channel(string $channel)
 * @method static Channel private(string $channel)
 * @method static Channel encryptedPrivate(string $channel)
 * @method static PresenceChannel join(string $channel)
 * @method static Channel listen(string $channel, string $event, Closure $callback)
 * @method static void connected(Closure $callback)
 * @method static void disconnect()
 * @method static void leave(string $channel)
 * @method static void leaveChannel(string $channel)
 * @method static void leaveAllChannels()
 * @method static string|null socketId()
 * @method static ResonanceManager extend(string $driver, Closure $callback)
 *
 * @see \ArtisanBuild\Resonance\ResonanceManager
 */
class Resonance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ResonanceManager::class;
    }
}
