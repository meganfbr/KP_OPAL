<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Penjadwalan';

    protected static ?string $navigationLabel = 'Manajemen Jadwal';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Jadwal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Select::make('laboratorium_id')
                            ->label('Laboratorium')
                            ->relationship('laboratorium', 'ruang')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih laboratorium untuk jadwal ini'),

                        Forms\Components\TextInput::make('kelompok')
                            ->label('Kelompok')
                            ->maxLength(255)
                            ->helperText('Contoh: A, B, C, atau kosongkan jika tidak ada kelompok'),

                        Forms\Components\Select::make('course_id')
                            ->label('Mata Kuliah')
                            ->relationship('course', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $course = Course::find($state);
                                    $startTime = $get('start_time');
                                    if ($course && $startTime) {
                                        $endTime = self::calculateEndTime($startTime, $course->sks);
                                        $set('end_time', $endTime);
                                    }
                                }
                            })
                            ->helperText('Pilih mata kuliah'),

                        Forms\Components\Select::make('lecturer_id')
                            ->label('Dosen')
                            ->relationship('lecturer', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Pilih dosen atau kosongkan jika belum ditentukan'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Jadwal Waktu')
                    ->schema([
                        Forms\Components\Select::make('day')
                            ->label('Hari')
                            ->options([
                                'Senin' => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis',
                                'Jumat' => 'Jumat',
                            ])
                            ->required()
                            ->helperText('Pilih hari perkuliahan'),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $courseId = $get('course_id');
                                    if ($courseId) {
                                        $course = Course::find($courseId);
                                        if ($course) {
                                            $endTime = self::calculateEndTime($state, $course->sks);
                                            $set('end_time', $endTime);
                                        }
                                    }
                                }
                            })
                            ->helperText('Jam mulai perkuliahan'),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->required()
                            ->disabled()
                            ->helperText('Otomatis dihitung berdasarkan SKS mata kuliah'),
                    ])
                    ->columns(3),
            ]);
    }

    private static function calculateEndTime(?string $startTime, ?int $sks): ?string
    {
        if (!$startTime || !$sks) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat('H:i', $startTime);
            $durationMinutes = $sks * 50; // 1 SKS = 50 menit
            $end = $start->addMinutes($durationMinutes);
            return $end->format('H:i');
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('time_range')
                    ->label('Jam')
                    ->getStateUsing(function ($record) {
                        return Carbon::parse($record->start_time)->format('H:i') . ' - ' .
                               Carbon::parse($record->end_time)->format('H:i');
                    })
                    ->sortable(['start_time'])
                    ->searchable(false),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('kelompok')
                    ->label('Kelompok')
                    ->placeholder('Semua')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('lecturer_display')
                    ->label('Dosen')
                    ->getStateUsing(function ($record) {
                        return $record->lecturer ? $record->lecturer->name : 'Belum Ditentukan';
                    })
                    ->color(fn ($record) => $record->lecturer ? 'success' : 'warning')
                    ->searchable(false),

                Tables\Columns\TextColumn::make('laboratorium.ruang')
                    ->label('Laboratorium')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('day')
                    ->label('Hari')
                    ->badge()
                    ->color('secondary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('laboratorium_id')
                    ->label('Laboratorium')
                    ->relationship('laboratorium', 'ruang')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('day')
                    ->label('Hari')
                    ->options([
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                    ]),

                Tables\Filters\SelectFilter::make('course_id')
                    ->label('Mata Kuliah')
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload(),
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
            ])
            ->groups([
                Tables\Grouping\Group::make('day')
                    ->label('Hari')
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $query, string $direction) =>
                        $query->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat') $direction")
                    ),
            ])
            ->defaultGroup('day')
            ->defaultSort('start_time');
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'view' => Pages\ViewSchedule::route('/{record}'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
