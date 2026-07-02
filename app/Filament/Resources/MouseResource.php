<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MouseResource\Pages;
use App\Models\Mouse;
use App\Filament\Resources\Concerns\HasHardwareAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MouseResource extends Resource
{
    use HasHardwareAccess;

    protected static ?string $model = Mouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-rays';
    protected static ?string $slug = 'mouse';
    protected static ?string $navigationLabel = 'Mouse';
    protected static ?string $modelLabel = 'Mouse';
    protected static ?string $navigationGroup = 'Data Hardware';
    protected static ?int $navigationSort = 8;

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
            'index' => Pages\ListMice::route('/'),
            'create' => Pages\CreateMouse::route('/create'),
            'edit' => Pages\EditMouse::route('/{record}/edit'),
        ];
    }
}