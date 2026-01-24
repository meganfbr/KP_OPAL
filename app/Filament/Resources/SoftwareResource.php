<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoftwareResource\Pages;
use App\Models\SoftwareDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SoftwareResource extends Resource
{
    protected static ?string $model = SoftwareDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'MASTER DATA';

    protected static ?string $navigationLabel = 'Daftar Software';

    protected static ?string $modelLabel = 'Software';

    protected static ?string $pluralModelLabel = 'Daftar Software';

    protected static ?string $slug = 'software';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Software')
                    ->description('Data master software yang tersentralisasi')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Software')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('PREMIERE')
                            ->helperText('Kode unik untuk identifikasi software (contoh: PREMIERE, VSCODE, XAMPP)')
                            ->regex('/^[A-Z0-9_]+$/')
                            ->validationMessages([
                                'regex' => 'Kode hanya boleh berisi huruf kapital, angka, dan underscore',
                            ]),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Software')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Adobe Premiere Pro')
                            ->helperText('Nama lengkap software'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(500)
                            ->rows(2)
                            ->placeholder('Deskripsi singkat tentang software ini')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Software')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->label('Kode Software')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('nama')
                            ->label('Nama Software'),
                        Infolists\Components\TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Digunakan di Laboratorium')
                    ->description('Daftar lab yang memiliki software ini')
                    ->schema([
                        Infolists\Components\TextEntry::make('labs')
                            ->label('')
                            ->state(function (SoftwareDetail $record): string {
                                $labs = $record->labs()->pluck('ruang')->toArray();
                                if (empty($labs)) {
                                    return 'Belum ada lab yang menggunakan software ini';
                                }
                                return implode(', ', $labs);
                            })
                            ->badge()
                            ->separator(',')
                            ->color('success'),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Dibutuhkan Mata Kuliah')
                    ->description('Daftar mata kuliah yang memerlukan software ini')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('courses')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('code')
                                    ->label('Kode')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nama Mata Kuliah'),
                                Infolists\Components\TextEntry::make('prodi.name')
                                    ->label('Program Studi')
                                    ->badge()
                                    ->color('warning'),
                            ])
                            ->columns(3)
                            ->placeholder('Belum ada mata kuliah yang memerlukan software ini'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Software')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('labs_count')
                    ->label('Digunakan di Lab')
                    ->counts('labs')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label('Dibutuhkan Mata Kuliah')
                    ->counts('courses')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('code')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSoftware::route('/'),
            'create' => Pages\CreateSoftware::route('/create'),
            'view' => Pages\ViewSoftware::route('/{record}'),
            'edit' => Pages\EditSoftware::route('/{record}/edit'),
        ];
    }
}
