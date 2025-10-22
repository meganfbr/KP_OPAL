<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Models\Prodi;
use App\Models\SoftwareDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Penjadwalan';

    protected static ?string $modelLabel = 'Mata Kuliah';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Mata Kuliah')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('sks')
                    ->label('SKS')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(6)
                    ->helperText('Jumlah Sistem Kredit Semester (1-6)'),

                Forms\Components\Select::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Program Studi')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Prodi')
                            ->maxLength(10)
                            ->helperText('Opsional: Kode singkat untuk program studi'),
                    ])
                    ->helperText('Pilih program studi atau buat baru'),

                Forms\Components\Select::make('software_requirements')
                    ->label('Software yang Dibutuhkan')
                    ->options(function () {
                        // Ambil software dari inventaris yang ada, bukan dari software_details
                        return \App\Models\Inventory::where('inventoriable_type', 'App\Models\SoftwareDetail')
                            ->whereNotNull('nama_barang')
                            ->where('nama_barang', '!=', '')
                            ->with('laboratorium')
                            ->get()
                            ->mapWithKeys(function ($inventory) {
                                $labName = $inventory->laboratorium?->ruang ?? 'Lab tidak diketahui';
                                return [$inventory->nama_barang => "{$inventory->nama_barang} (Lab: {$labName})"];
                            })
                            ->unique()
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->helperText('Pilih software yang dibutuhkan. Hanya software yang tersedia di inventaris lab yang ditampilkan.')
                    ->optionsLimit(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Mata Kuliah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sks')
                    ->label('SKS')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('prodi.name')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('software_count')
                    ->label('Software Dibutuhkan')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (empty($record->software_requirements)) {
                            return '0';
                        }
                        $count = count($record->software_requirements);
                        return $count . ' Software';
                    })
                    ->color('info')
                    ->tooltip(function ($record) {
                        if (empty($record->software_requirements)) {
                            return 'Tidak ada software yang dibutuhkan';
                        }
                        return 'Software: ' . collect($record->software_requirements)->join(', ');
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('software_requirements')
                    ->label('Software')
                    ->options(function () {
                        // Ambil daftar software dari inventaris untuk filter
                        return \App\Models\Inventory::where('inventoriable_type', 'App\Models\SoftwareDetail')
                            ->whereNotNull('nama_barang')
                            ->where('nama_barang', '!=', '')
                            ->pluck('nama_barang', 'nama_barang')
                            ->unique()
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['values'])) {
                            $query->where(function ($query) use ($data) {
                                foreach ($data['values'] as $software) {
                                    $query->orWhereJsonContains('software_requirements', $software);
                                }
                            });
                        }
                    }),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
