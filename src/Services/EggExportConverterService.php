<?php

namespace ScrambleEggConverter\Services;

use App\Enums\EggFormat;
use App\Models\Egg;
use App\Services\Eggs\Sharing\EggExporterService;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class EggExportConverterService
{
    public function __construct(private EggExporterService $eggExporterService) {}

    public function export(Egg $egg, string $target, ?string $pelicanFormat = null): string
    {
        if ($target === 'pelican') {
            $format = $pelicanFormat === 'json' ? EggFormat::JSON : EggFormat::YAML;

            return $this->eggExporterService->handle($egg->id, $format);
        }

        if ($target === 'pterodactyl') {
            $pelicanExport = json_decode($this->eggExporterService->handle($egg->id, EggFormat::JSON), true);
            if (!is_array($pelicanExport)) {
                throw new InvalidArgumentException('Invalid Pelican export payload.');
            }

            return json_encode($this->convertToPterodactyl($pelicanExport), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        throw new InvalidArgumentException("Unknown export target: {$target}");
    }

    /**
     * @param  array<string, mixed>  $pelican
     * @return array<string, mixed>
     */
    private function convertToPterodactyl(array $pelican): array
    {
        $ptero = $pelican;

        $ptero['_comment'] = 'DO NOT EDIT: FILE GENERATED AUTOMATICALLY BY PTERODACTYL PANEL - PTERODACTYL.IO';
        $ptero['meta']['version'] = 'PTDL_v2';
        $ptero['meta']['update_url'] = null;

        unset($ptero['uuid'], $ptero['tags'], $ptero['image']);

        if (isset($ptero['startup_commands']) && is_array($ptero['startup_commands'])) {
            $firstStartup = Arr::first($ptero['startup_commands']);
            if (is_string($firstStartup) && $firstStartup !== '') {
                $ptero['startup'] = $firstStartup;
            }
            unset($ptero['startup_commands']);
        }

        if (isset($ptero['variables']) && is_array($ptero['variables'])) {
            $ptero['variables'] = array_map(function ($variable) {
                if (!is_array($variable)) {
                    return $variable;
                }

                unset($variable['sort']);
                $variable['rules'] = is_array($variable['rules'] ?? null)
                    ? implode('|', $variable['rules'])
                    : ($variable['rules'] ?? '');
                $variable['field_type'] = 'text';

                return $variable;
            }, $ptero['variables']);
        }

        if (isset($ptero['config']['files']) && is_string($ptero['config']['files'])) {
            $ptero['config']['files'] = str_replace(
                ['server.environment', 'server.allocations.default'],
                ['server.build.env', 'server.build.default'],
                $ptero['config']['files']
            );
        }

        if (isset($ptero['features']) && is_array($ptero['features']) && count($ptero['features']) === 0) {
            $ptero['features'] = null;
        }

        $ordered = [
            '_comment' => $ptero['_comment'] ?? null,
            'meta' => $ptero['meta'] ?? [],
            'exported_at' => $ptero['exported_at'] ?? null,
            'name' => $ptero['name'] ?? null,
            'author' => $ptero['author'] ?? null,
            'description' => $ptero['description'] ?? null,
            'features' => $ptero['features'] ?? null,
            'docker_images' => $ptero['docker_images'] ?? null,
            'file_denylist' => $ptero['file_denylist'] ?? [],
        ];

        if (isset($ptero['startup'])) {
            $ordered['startup'] = $ptero['startup'];
        }

        foreach ($ptero as $key => $value) {
            if (!array_key_exists($key, $ordered)) {
                $ordered[$key] = $value;
            }
        }

        return $ordered;
    }
}
