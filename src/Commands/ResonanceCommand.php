<?php

namespace ArtisanBuild\Resonance\Commands;

use Illuminate\Console\Command;

class ResonanceCommand extends Command
{
    public $signature = 'resonance';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
