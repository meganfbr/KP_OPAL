<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenyimpananResource\Pages;
use App\Models\Penyimpanan;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PenyimpananResource extends Resource
{
    protected static ?string $model = Penyimpanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $slug = 'penyimpanan';

    protected static ?string $navigationLabel = 'Hardisk / Penyimpanan';

    protected static ?string $modelLabel = 'Hardisk / Penyimpanan';

    protected static ?string $navigationGroup = 'Data Hardware';

    protected static ?int $navigationSort = 3;

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
                ->minValue(0)
                ->default(0)
                ->required(),
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
            'index' => Pages\ListPenyimpanans::route('/'),
            'create' => Pages\CreatePenyimpanan::route('/create'),
            'edit' => Pages\EditPenyimpanan::route('/{record}/edit'),
        ];
    }
}