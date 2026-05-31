<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MotherboardResource\Pages;
use App\Models\Motherboard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MotherboardResource extends Resource
{
    protected static ?string $model = Motherboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $slug = 'motherboard';

    protected static ?string $navigationLabel = 'Motherboard';

    protected static ?string $modelLabel = 'Motherboard';

    protected static ?string $navigationGroup = 'Data Hardware';

    protected static ?int $navigationSort = 1;

    protected static function canManageHardware(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('super_admin');
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('merk')
                ->label('Merk')
                ->required()
                ->maxLength(255),

            TextInput::make('stok')
                ->label('Jumlah')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(false)
                ->helperText('Jumlah dihitung dari pemakaian hardware pada Rekap Inventaris bulan terbaru.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('merk')
                    ->label('Merk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stok')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->badge(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => static::canManageHardware()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (): bool => static::canManageHardware()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canManageHardware()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMotherboards::route('/'),
            'create' => Pages\CreateMotherboard::route('/create'),
            'edit' => Pages\EditMotherboard::route('/{record}/edit'),
        ];
    }
}