<?php

namespace ScrambleEggConverter\Filament\Admin\Pages;

use App\Enums\TablerIcon;
use App\Models\Egg;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScrambleEggConverter extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::Eggs;

    protected static ?int $navigationSort = 11;

    protected string $view = 'scramble-egg-converter::filament.admin.pages.scramble-egg-converter';

    public static function getNavigationLabel(): string
    {
        return 'Scramble Egg Converter';
    }

    public function getTitle(): string
    {
        return 'Scramble Egg Converter';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Developer';
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Egg::query()
                    ->withCount('servers')
            )
            ->defaultSort('name', 'asc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('id', $direction)),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('name', $direction))
                    ->description(fn (Egg $record): ?string => $record->description ? str($record->description)->limit(80)->toString() : null)
                    ->wrap(),
                TextColumn::make('author')
                    ->searchable()
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('author', $direction))
                    ->toggleable(),
                TextColumn::make('servers_count')
                    ->label('Servers')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('servers_count', $direction)),
            ])
            ->recordActions([
                Action::make('exclude_export_pelican')
                    ->label('Pelican')
                    ->color('gray')
                    ->icon(TablerIcon::Download)
                    ->button()
                    ->schema([
                        Select::make('format')
                            ->label('Pelican Format')
                            ->options([
                                'yaml' => 'YAML',
                                'json' => 'JSON',
                            ])
                            ->default('yaml')
                            ->required(),
                    ])
                    ->action(fn (Egg $record, array $data) => $this->download($record->id, 'pelican', $data['format'] ?? 'yaml')),
                Action::make('exclude_export_pterodactyl')
                    ->label('Pterodactyl')
                    ->color('primary')
                    ->icon(TablerIcon::Download)
                    ->button()
                    ->action(fn (Egg $record) => $this->download($record->id, 'pterodactyl')),
            ])
            ->emptyStateHeading('No eggs found.');
    }

    public function download(int $eggId, string $target, ?string $pelicanFormat = null): ?StreamedResponse
    {
        if (!in_array($target, ['pelican', 'pterodactyl'], true)) {
            Notification::make()
                ->danger()
                ->title('Invalid export type.')
                ->send();

            return null;
        }

        $egg = Egg::query()->find($eggId);
        if (!$egg) {
            Notification::make()
                ->danger()
                ->title('Egg not found.')
                ->send();

            return null;
        }

        if ($target === 'pelican' && !in_array($pelicanFormat, ['json', 'yaml'], true)) {
            $pelicanFormat = 'yaml';
        }

        $payload = app(\ScrambleEggConverter\Services\EggExportConverterService::class)->export($egg, $target, $pelicanFormat); // @phpstan-ignore myCustomRules.forbiddenGlobalFunctions
        $isPelican = $target === 'pelican';
        $extension = $isPelican ? ($pelicanFormat === 'json' ? 'json' : 'yaml') : 'json';
        $fileName = sprintf('%s-%s.%s', $target, $egg->getKebabName(), $extension);

        return response()->streamDownload(function () use ($payload) {
            echo $payload;
        }, $fileName, [
            'Content-Type' => $isPelican
                ? (($pelicanFormat === 'json') ? 'application/json' : 'application/x-yaml')
                : 'application/json',
        ]);
    }
}
