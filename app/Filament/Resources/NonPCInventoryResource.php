<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NonPCInventoryResource\Pages;
use App\Models\Inventory;
use App\Models\NonPCDetail;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NonPCInventoryResource extends Resource
{
    protected static ?string $model = NonPCDetail::class;

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    protected static ?string $modelLabel = 'Inventaris Non-PC';
    protected static ?string $pluralModelLabel = 'Inventaris Non-PC';
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Master Non-PC')
                    ->description('Daftarkan jenis barang non-PC secara global di sini.')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Barang')
                            ->required()
                            ->placeholder('Contoh: Proyektor'),
                        TextInput::make('merk')
                            ->label('Merk')
                            ->required()
                            ->placeholder('Contoh: Epson'),
                        TextInput::make('model')
                            ->label('Model/Tipe')
                            ->required()
                            ->placeholder('Contoh: EB-X400'),
                        Textarea::make('spesifikasi')
                            ->label('Spesifikasi Utama')
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('merk')->searchable()->sortable(),
                TextColumn::make('model')->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // No more lab filters for global master data
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNonPCInventories::route('/'),
            'create' => Pages\CreateNonPCInventory::route('/create'),
            'edit' => Pages\EditNonPCInventory::route('/{record}/edit'),
        ];
    }
}
