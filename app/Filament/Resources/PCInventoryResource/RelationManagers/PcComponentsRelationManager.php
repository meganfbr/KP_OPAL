<?php

namespace App\Filament\Resources\PCInventoryResource\RelationManagers;

use App\Models\DVD;
use App\Models\Keyboard;
use App\Models\Monitor;
use App\Models\Motherboard;
use App\Models\Mouse;
use App\Models\Penyimpanan;
use App\Models\Processor;
use App\Models\RAM;
use App\Models\VGA;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PcComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'pcComponents';

    protected static ?string $title = 'Detail Spesifikasi PC';

    protected static ?string $modelLabel = 'Detail Spesifikasi';

    protected static ?string $pluralModelLabel = 'Detail Spesifikasi PC';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('komponen')
                ->label('Komponen')
                ->options([
                    'Motherboard' => 'Motherboard',
                    'Processor' => 'Processor',
                    'Hardisk' => 'Hardisk',
                    'VGA' => 'VGA',
                    'RAM' => 'RAM',
                    'DVD' => 'DVD',
                    'Keyboard' => 'Keyboard',
                    'Mouse' => 'Mouse',
                    'Monitor' => 'Monitor',
                ])
                ->required()
                ->disabled(fn (?string $operation): bool => $operation === 'edit')
                ->dehydrated(),

            Select::make('motherboard_id')
                ->label('Merk Motherboard')
                ->options(fn () => Motherboard::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'Motherboard'),

            Select::make('processor_id')
                ->label('Merk Processor')
                ->options(fn () => Processor::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'Processor'),

            Select::make('penyimpanan_id')
                ->label('Merk Hardisk / Penyimpanan')
                ->options(fn () => Penyimpanan::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'Hardisk'),

            Select::make('vga_id')
                ->label('Merk VGA')
                ->options(fn () => VGA::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'VGA'),

            Select::make('ram_id')
                ->label('Merk RAM')
                ->options(fn () => RAM::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'RAM'),

            Select::make('dvd_id')
                ->label('Merk DVD')
                ->options(fn () => DVD::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'DVD'),

            Select::make('keyboard_id')
                ->label('Merk Keyboard')
                ->options(fn () => Keyboard::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'Keyboard'),
            Select::make('mouse_id')
                ->label('Merk Mouse')
                ->options(fn () => Mouse::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'Mouse'),

            Select::make('monitor_id')
                ->label('Merk Monitor')
                ->options(fn () => Monitor::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('komponen') === 'Monitor'),

            Select::make('kondisi')
                ->label('Kondisi')
                ->options([
                    'Baik' => 'Baik',
                    'Kurang Baik' => 'Kurang Baik',
                    'Rusak' => 'Rusak',
                    '-' => '-',
                ])
                ->required()
                ->live(),

            Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3)
                ->placeholder('Isi keterangan jika kondisi Kurang Baik, Rusak, atau barang tidak tersedia.')
                ->visible(fn (Get $get): bool => in_array($get('kondisi'), ['Kurang Baik', 'Rusak', '-'], true)),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('urutan')
            ->columns([
                TextColumn::make('komponen')
                    ->label('Komponen')
                    ->sortable(),

                TextColumn::make('detail_merk')
                    ->label('Merk / Detail')
                    ->state(fn ($record): string => $record->detail_merk),

                TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Kurang Baik' => 'warning',
                        'Rusak' => 'danger',
                        '-' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->wrap()
                    ->placeholder('-'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Komponen')
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin')),
            ])
            ->bulkActions([]);
    }
}