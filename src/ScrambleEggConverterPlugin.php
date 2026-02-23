<?php

namespace ScrambleEggConverter;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ScrambleEggConverterPlugin implements Plugin
{
    public function getId(): string
    {
        return 'scramble-egg-converter';
    }

    public function register(Panel $panel): void
    {
        $panelTitle = str($panel->getId())->title()->toString();

        $panel->discoverPages(
            in: plugin_path($this->getId(), "src/Filament/{$panelTitle}/Pages"),
            for: "ScrambleEggConverter\\Filament\\{$panelTitle}\\Pages"
        );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
