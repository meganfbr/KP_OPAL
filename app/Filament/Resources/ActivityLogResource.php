<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $slug = 'activity-log';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 99;

    /**
     * Hanya super_admin yang bisa mengakses log aktivitas
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('Aktor (User)')
                    ->searchable()
                    ->default('-')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('causer.roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('warning')
                    ->default('-'),

                TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Tambah',
                        'updated' => 'Edit',
                        'deleted' => 'Hapus',
                        default => ucfirst($state),
                    }),

                TextColumn::make('log_name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'user' => 'User',
                        'inventaris' => 'Inventaris',
                        'rekap-inventaris' => 'Rekap Inventaris',
                        'hardware' => 'Hardware',
                        'monitor' => 'Monitor',
                        'jadwal' => 'Jadwal',
                        default => ucfirst($state),
                    }),

                TextColumn::make('subject_type')
                    ->label('Data (Model)')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->toggleable(),

                TextColumn::make('properties')
                    ->label('Detail Perubahan')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) return '-';

                        $props = is_string($state) ? json_decode($state, true) : (array) $state;
                        $parts = [];

                        if (isset($props['old']) && isset($props['attributes'])) {
                            foreach ($props['attributes'] as $key => $newVal) {
                                $oldVal = $props['old'][$key] ?? '-';
                                $parts[] = "{$key}: {$oldVal} → {$newVal}";
                            }
                        } elseif (isset($props['attributes'])) {
                            foreach ($props['attributes'] as $key => $val) {
                                $parts[] = "{$key}: {$val}";
                            }
                        }

                        return implode(', ', array_slice($parts, 0, 3)) . (count($parts) > 3 ? '...' : '');
                    })
                    ->limit(60)
                    ->tooltip(function ($record) {
                        $props = $record->properties->toArray();
                        if (empty($props)) return null;
                        return json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Tambah',
                        'updated' => 'Edit',
                        'deleted' => 'Hapus',
                    ]),

                SelectFilter::make('log_name')
                    ->label('Kategori')
                    ->options([
                        'user' => 'User',
                        'inventaris' => 'Inventaris',
                        'rekap-inventaris' => 'Rekap Inventaris',
                        'hardware' => 'Hardware',
                        'monitor' => 'Monitor',
                    ]),

                SelectFilter::make('causer_id')
                    ->label('User')
                    ->relationship('causer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Detail Log Aktivitas')
                    ->modalContent(fn (Activity $record) => view('filament.resources.activity-log-detail', ['record' => $record])),
            ])
            ->bulkActions([])
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
