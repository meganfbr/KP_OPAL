<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $slug = 'laboran';

    protected static ?string $navigationLabel = 'Data Laboran';

    protected static ?string $modelLabel = 'Laboran';

    protected static ?string $navigationGroup = 'MASTER DATA';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create'),
                TextInput::make('npp')
                    ->label('NPP/NIM')
                    ->required()
                    ->maxLength(50),
                TextInput::make('no_phone')
                    ->label('No HP')
                    ->required()
                    ->maxLength(15),
                Select::make('roles')
                    ->label('Role')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable(),
                FileUpload::make('foto')
                    ->label('Foto Laboran')
                    ->image()
                    ->directory('laboran-photos') // Simpan foto di storage/app/public/laboran-photos
                    ->nullable()
                    ->preserveFilenames()
                    ->columnSpanFull(),
                DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->native(false)
                    ->nullable(),
                DatePicker::make('tanggal_keluar')
                    ->label('Tanggal Keluar / Kontrak Berakhir')
                    ->native(false)
                    ->nullable()
                    ->helperText('Jika diisi dan sudah lewat, akun akan otomatis dinonaktifkan.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('is_active', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('npp')
                    ->label('NPP/NIM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Tgl Masuk')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('tanggal_keluar')
                    ->label('Tgl Keluar / Kontrak Berakhir')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-')
                    ->color(fn ($record) => 
                        $record->tanggal_keluar !== null && $record->tanggal_keluar < Carbon::today()
                            ? 'danger'
                            : ($record->tanggal_keluar !== null && $record->tanggal_keluar <= Carbon::today()->addMonth()
                                ? 'warning'
                                : null)
                    ),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status Akun')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $term = strtolower(trim($search));
                        if ($term === 'aktif') {
                            return $query->where('is_active', true);
                        } elseif ($term === 'nonaktif') {
                            return $query->where('is_active', false);
                        }
                        return $query;
                    }),
            ])
            ->filters([
                // Filter Status Aktif/Nonaktif
                SelectFilter::make('status')
                    ->label('Status Akun')
                    ->searchable()
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'aktif' => $query->where('is_active', true),
                            'nonaktif' => $query->where('is_active', false),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return match ($data['value'] ?? null) {
                            'aktif' => 'Status Akun: Aktif',
                            'nonaktif' => 'Status Akun: Nonaktif',
                            default => null,
                        };
                    }),

                // Filter Status Kontrak
                SelectFilter::make('status_kontrak')
                    ->label('Status Kontrak')
                    ->options([
                        'berakhir_bulan_ini' => 'Berakhir Bulan Ini',
                        'berakhir_tahun_ini' => 'Berakhir Tahun Ini',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->contractStatus($data['value']);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return match ($data['value'] ?? null) {
                            'berakhir_bulan_ini' => 'Kontrak Berakhir Bulan Ini',
                            'berakhir_tahun_ini' => 'Kontrak Berakhir Tahun Ini',
                            default => null,
                        };
                    }),

                // Filter Periode (Bulan/Tahun + Status Periode)
                Filter::make('periode')
                    ->form([
                        Select::make('bulan')
                            ->label('Bulan Periode')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ])
                            ->default(now()->month),
                        Select::make('tahun')
                            ->label('Tahun Periode')
                            ->options(function () {
                                $tahunSekarang = (int) date('Y');
                                $options = [];
                                for ($y = $tahunSekarang - 3; $y <= $tahunSekarang + 2; $y++) {
                                    $options[$y] = (string) $y;
                                }
                                return $options;
                            })
                            ->default(now()->year),
                        Select::make('status_periode')
                            ->label('Status Periode')
                            ->options([
                                'semua' => 'Semua',
                                'aktif' => 'Aktif',
                                'tidak_aktif' => 'Tidak Aktif',
                                'kontrak_berakhir' => 'Kontrak Berakhir Bulan Ini',
                            ])
                            ->default('semua'),
                    ])
                    ->columns(3)
                    ->query(function (Builder $query, array $data): Builder {
                        $bulan = (int) ($data['bulan'] ?? 0);
                        $tahun = (int) ($data['tahun'] ?? 0);
                        $statusPeriode = $data['status_periode'] ?? 'semua';

                        if (!$bulan || !$tahun) {
                            return $query;
                        }

                        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();
                        $akhirBulan = $awalBulan->copy()->endOfMonth()->endOfDay();

                        if ($statusPeriode === 'aktif') {
                            // Aktif pada periode: tanggal_masuk <= akhir_bulan AND (tanggal_keluar null OR >= awal_bulan) AND is_active
                            $query->where(function (Builder $q) use ($awalBulan, $akhirBulan) {
                                $q->where(function (Builder $inner) use ($akhirBulan) {
                                    $inner->whereNull('tanggal_masuk')
                                          ->orWhereDate('tanggal_masuk', '<=', $akhirBulan);
                                })
                                ->where(function (Builder $inner) use ($awalBulan) {
                                    $inner->whereNull('tanggal_keluar')
                                          ->orWhereDate('tanggal_keluar', '>=', $awalBulan);
                                })
                                ->where('is_active', true);
                            });
                        } elseif ($statusPeriode === 'tidak_aktif') {
                            // Tidak aktif: tanggal_keluar < awal_bulan OR is_active = false
                            $query->where(function (Builder $q) use ($awalBulan) {
                                $q->where(function (Builder $inner) use ($awalBulan) {
                                    $inner->whereNotNull('tanggal_keluar')
                                          ->whereDate('tanggal_keluar', '<', $awalBulan);
                                })
                                ->orWhere('is_active', false);
                            });
                        } elseif ($statusPeriode === 'kontrak_berakhir') {
                            // Kontrak berakhir di bulan/tahun ini
                            $query->whereNotNull('tanggal_keluar')
                                  ->whereYear('tanggal_keluar', $tahun)
                                  ->whereMonth('tanggal_keluar', $bulan);
                        }
                        // 'semua' => no additional filter

                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        $bulan = (int) ($data['bulan'] ?? 0);
                        $tahun = (int) ($data['tahun'] ?? 0);

                        if ($bulan && $tahun) {
                            $namaBulan = Carbon::create($tahun, $bulan, 1)->translatedFormat('F');
                            $indicators['bulan'] = "Periode: {$namaBulan} {$tahun}";
                        }

                        $statusPeriode = $data['status_periode'] ?? 'semua';
                        if ($statusPeriode !== 'semua') {
                            $label = match ($statusPeriode) {
                                'aktif' => 'Aktif',
                                'tidak_aktif' => 'Tidak Aktif',
                                'kontrak_berakhir' => 'Kontrak Berakhir Bulan Ini',
                                default => null,
                            };
                            if ($label) {
                                $indicators['status_periode'] = "Status Periode: {$label}";
                            }
                        }

                        return $indicators;
                    }),

                // Filter by Role/Laboratorium
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple()
                    ->label('Role / Laboratorium'),

                // Filter Tanggal Masuk (dari - sampai)
                Filter::make('tanggal_masuk')
                    ->form([
                        DatePicker::make('tanggal_masuk_dari')
                            ->label('Tanggal Masuk Dari')
                            ->native(false),
                        DatePicker::make('tanggal_masuk_sampai')
                            ->label('Tanggal Masuk Sampai')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['tanggal_masuk_dari'], fn (Builder $q, $date) => $q->whereDate('tanggal_masuk', '>=', $date))
                            ->when($data['tanggal_masuk_sampai'], fn (Builder $q, $date) => $q->whereDate('tanggal_masuk', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_masuk_dari'] ?? null) {
                            $indicators['tanggal_masuk_dari'] = 'Masuk dari: ' . Carbon::parse($data['tanggal_masuk_dari'])->format('d M Y');
                        }
                        if ($data['tanggal_masuk_sampai'] ?? null) {
                            $indicators['tanggal_masuk_sampai'] = 'Masuk sampai: ' . Carbon::parse($data['tanggal_masuk_sampai'])->format('d M Y');
                        }
                        return $indicators;
                    }),

                // Filter Tanggal Keluar / Kontrak Berakhir (dari - sampai)
                Filter::make('tanggal_keluar')
                    ->form([
                        DatePicker::make('tanggal_keluar_dari')
                            ->label('Tgl Keluar Dari')
                            ->native(false),
                        DatePicker::make('tanggal_keluar_sampai')
                            ->label('Tgl Keluar Sampai')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['tanggal_keluar_dari'], fn (Builder $q, $date) => $q->whereDate('tanggal_keluar', '>=', $date))
                            ->when($data['tanggal_keluar_sampai'], fn (Builder $q, $date) => $q->whereDate('tanggal_keluar', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['tanggal_keluar_dari'] ?? null) {
                            $indicators['tanggal_keluar_dari'] = 'Keluar dari: ' . Carbon::parse($data['tanggal_keluar_dari'])->format('d M Y');
                        }
                        if ($data['tanggal_keluar_sampai'] ?? null) {
                            $indicators['tanggal_keluar_sampai'] = 'Keluar sampai: ' . Carbon::parse($data['tanggal_keluar_sampai'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('toggleActive')
                        ->label(fn ($record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => ($record->is_active ? 'Nonaktifkan' : 'Aktifkan') . ' Akun')
                        ->modalDescription(fn ($record) => 'Apakah Anda yakin ingin ' 
                            . ($record->is_active ? 'menonaktifkan' : 'mengaktifkan') 
                            . ' akun ' . $record->name . '?')
                        ->action(fn ($record) => $record->update(['is_active' => !$record->is_active]))
                        ->visible(fn () => auth()->user()->hasRole('super_admin'))
                        ->hidden(fn ($record) => $record->hasRole('super_admin')),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn () => auth()->user()->hasRole('super_admin')),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Akun Laboran')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\ImageEntry::make('foto')
                                    ->label('Foto')
                                    ->circular()
                                    ->columnSpan(1),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('name')->label('Nama'),
                                    Infolists\Components\TextEntry::make('npp')->label('NPP/NIM'),
                                    Infolists\Components\TextEntry::make('email')->label('Email'),
                                    Infolists\Components\TextEntry::make('no_phone')->label('No HP'),
                                    Infolists\Components\TextEntry::make('roles.name')->label('Role')->badge(),
                                    Infolists\Components\TextEntry::make('tanggal_masuk')
                                        ->label('Tanggal Masuk')
                                        ->date('d M Y')
                                        ->placeholder('-'),
                                    Infolists\Components\TextEntry::make('tanggal_keluar')
                                        ->label('Tanggal Keluar / Kontrak Berakhir')
                                        ->date('d M Y')
                                        ->placeholder('-')
                                        ->color(fn ($record) =>
                                            $record->tanggal_keluar !== null && $record->tanggal_keluar < Carbon::today()
                                                ? 'danger'
                                                : null
                                        ),
                                    Infolists\Components\TextEntry::make('is_active')
                                        ->label('Status Akun')
                                        ->badge()
                                        ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                                        ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                                ])->columns(2)->columnSpan(2),
                            ]),
                    ]),
            ]);
    }
}
