<?php

namespace App\Filament\Resources\Concerns;

use App\Services\HardwareUsageCounter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait UsesSimpleHardwareResource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('merk')
                ->label('Merk / Tipe')
                ->required()
                ->maxLength(255)
                ->placeholder('Contoh: ASUS H61M, Intel Core i5, Kingston 8GB'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('merk')
                    ->label('Merk / Tipe')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_pemakaian')
                    ->label('Jumlah')
                    ->state(function (Model $record, $livewire): int {
                        $period = static::getHardwareSelectedPeriod($livewire);

                        return HardwareUsageCounter::countUsage(
                            record: $record,
                            componentColumn: static::$usageColumn,
                            bulan: $period['bulan'],
                            tahun: $period['tahun'],
                        );
                    })
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('bulan_periode')
                    ->label('Bulan')
                    ->options(static::monthOptions())
                    ->default((int) now()->month)
                    ->query(fn (Builder $query): Builder => $query),

                SelectFilter::make('tahun_periode')
                    ->label('Tahun')
                    ->options(static::yearOptions())
                    ->default((int) now()->year)
                    ->query(fn (Builder $query): Builder => $query),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn (): bool => static::canManageHardware()),
                Tables\Actions\DeleteAction::make()->visible(fn (): bool => static::canManageHardware()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(fn (): bool => static::canManageHardware()),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return static::canManageHardware();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canManageHardware();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canManageHardware();
    }

    public static function canDeleteAny(): bool
    {
        return static::canManageHardware();
    }

    protected static function canManageHardware(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['super_admin', 'admin', 'Admin', 'Super Admin']);
    }

    protected static function getHardwareSelectedPeriod($livewire): array
    {
        $filters = $livewire->tableFilters ?? [];

        return [
            'bulan' => (int) ($filters['bulan_periode']['value'] ?? now()->month),
            'tahun' => (int) ($filters['tahun_periode']['value'] ?? now()->year),
        ];
    }

    protected static function monthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    protected static function yearOptions(): array
    {
        $tahunSekarang = (int) now()->year;

        return collect(range($tahunSekarang - 5, $tahunSekarang + 1))
            ->mapWithKeys(fn (int $tahun): array => [$tahun => $tahun])
            ->toArray();
    }
}