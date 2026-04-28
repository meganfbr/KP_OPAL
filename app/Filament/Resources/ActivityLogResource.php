<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 100;

    public static function getNavigationLabel(): string
    {
        $user = Auth::user();
        if ($user && $user->hasRole('super_admin')) {
            return 'Aktivitas Sistem';
        }
        return 'Aktivitas Saya';
    }

    public static function getPluralModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public static function getModelLabel(): string
    {
        return 'Log Aktivitas';
    }

    /**
     * Scope the query based on user role.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && !$user->hasRole('super_admin')) {
            // Laboran only see their own logs
            return $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super_admin');

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->hidden(!$isSuperAdmin),

                TextColumn::make('laboratorium.ruang')
                    ->label('Lab')
                    ->badge()
                    ->color('info')
                    ->default('-')
                    ->hidden(!$isSuperAdmin),

                TextColumn::make('aksi')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CREATE' => 'success',
                        'UPDATE' => 'warning',
                        'DELETE' => 'danger',
                        'LOGIN' => 'info',
                        'LOGOUT' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('modul')
                    ->label('Modul')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->searchable()
                    ->wrap(),
            ])
            ->filters([
                // Only Super Admin gets these filters
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdmin),

                SelectFilter::make('lab_id')
                    ->label('Laboratorium')
                    ->relationship('laboratorium', 'ruang')
                    ->visible($isSuperAdmin),

                SelectFilter::make('aksi')
                    ->options([
                        'CREATE' => 'CREATE',
                        'UPDATE' => 'UPDATE',
                        'DELETE' => 'DELETE',
                        'LOGIN' => 'LOGIN',
                        'LOGOUT' => 'LOGOUT',
                    ])
                    ->visible($isSuperAdmin),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->visible($isSuperAdmin),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
