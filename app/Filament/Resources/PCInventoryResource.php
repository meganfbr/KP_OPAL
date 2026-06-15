<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PCInventoryResource\Pages;
use App\Models\DVD;
use App\Models\Inventory;
use App\Models\InventoryPcComponent;
use App\Models\Keyboard;
use App\Models\Laboratorium;
use App\Models\Monitor;
use App\Models\Motherboard;
use App\Models\Mouse;
use App\Models\Penyimpanan;
use App\Models\Processor;
use App\Models\RAM;
use App\Models\User;
use App\Models\VGA;
use App\Services\HardwareUsageCounter;
use App\Services\InventoryPcIdService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PCInventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $modelLabel = 'Inventaris PC';

    protected static ?string $pluralModelLabel = 'Inventaris PC';

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    protected static function canManageInventoryPc(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['super_admin', 'admin', 'Admin', 'Super Admin']);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (static::canManageInventoryPc()) {
            return true;
        }

        return $user->roles->pluck('name')->contains(fn ($n) => str_starts_with($n, 'Laboran_'));
    }

    public static function canCreate(): bool
    {
        return static::canManageInventoryPc();
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canManageInventoryPc();
    }

    public static function canDeleteAny(): bool
    {
        return static::canManageInventoryPc();
    }

    public static function getEloquentQuery(): Builder
    {
        $period = static::getActivePeriod();

        $query = parent::getEloquentQuery()
            ->whereNull('inventoriable_type')
            ->where('bulan', $period['bulan'])
            ->where('tahun', $period['tahun'])
            ->with(['asal', 'lokasi', 'petugas', 'pcDetail', 'pcComponents']);

        $user = auth()->user();

        if ($user && ! static::canManageInventoryPc()) {
            $laboranRoles = $user->roles->filter(fn ($r) => str_starts_with($r->name, 'Laboran_'));

            $authorizedLabNames = [];

            foreach ($laboranRoles as $role) {
                $labSlug = str_replace('Laboran_', '', $role->name);
                $authorizedLabNames[] = 'LAB ' . strtoupper($labSlug);
            }

            $labIds = Laboratorium::whereIn('ruang', $authorizedLabNames)->pluck('id')->toArray();

            if (! empty($labIds)) {
                $query->whereIn('lokasi_id', $labIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi PC')
                ->description('Admin tidak mengisi bulan/tahun. PC baru otomatis masuk Gudang dan periode yang sedang dibuka.')
                ->schema([
                    TextInput::make('kode_unique')
                        ->label('Kode BIUM')
                        ->nullable()
                        ->maxLength(50)
                        ->placeholder('Boleh kosong / contoh: LAB-A-01'),

                    Select::make('pc_detail.posisi')
                        ->label('Posisi')
                        ->options([
                            'Dosen' => 'Dosen',
                            'Laboran' => 'Laboran',
                            'Client' => 'Client',
                        ])
                        ->default('Client')
                        ->required(),
                ])
                ->columns(2),

            Section::make('Detail Komponen PC')
                ->description('Komponen fixed. Detail diambil dari Data Hardware.')
                ->schema([
                    Grid::make(3)->schema(static::componentFields()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_inventaris')
                    ->label('ID')
                    ->formatStateUsing(fn ($state) => $state ? InventoryPcIdService::format((int) $state) : '-')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('no_pc')
                    ->label('No PC')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('-')
                    ->action(
                        ViewAction::make('detail_pc')
                            ->modalHeading(fn (Inventory $record): string => 'Detail PC ' . ($record->no_pc ?: '-'))
                            ->infolist(fn (Infolist $infolist): Infolist => static::infolist($infolist))
                    ),

                TextColumn::make('kode_unique')
                    ->label('Kode BIUM')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lokasi.ruang')
                    ->label('Lokasi Terkini')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('asal.ruang')
                    ->label('Asal')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('petugas.name')
                    ->label('Petugas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->placeholder('Aktif'),
            ])
            ->filters([
                SelectFilter::make('lokasi_id')
                    ->label('Lokasi')
                    ->options(fn () => Laboratorium::query()->orderBy('ruang')->pluck('ruang', 'id')),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (): bool => static::canManageInventoryPc())
                    ->before(function (Inventory $record): void {
                        $record->pcComponents()->delete();
                        $record->pcDetail()->delete();
                    })
                    ->after(function (Inventory $record): void {
                        InventoryPcIdService::resequence((int) $record->bulan, (int) $record->tahun);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('pindahkan')
                        ->label('Pindahkan')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('success')
                        ->visible(fn (): bool => static::canManageInventoryPc())
                        ->form([
                            Select::make('lab_tujuan_id')
                                ->label('Pilih Lokasi Tujuan')
                                ->options(fn () => Laboratorium::query()->orderBy('ruang')->pluck('ruang', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->modalHeading('Pindahkan PC')
                        ->modalSubmitActionLabel('Pindahkan')
                        ->action(function (Collection $records, array $data): void {
                            $period = static::getActivePeriod();

                            foreach ($records as $record) {
                                static::movePcToLocation($record, (int) $data['lab_tujuan_id'], $period['bulan'], $period['tahun']);
                            }

                            HardwareUsageCounter::recalculate($period['bulan'], $period['tahun']);

                            Notification::make()
                                ->title('PC berhasil dipindahkan')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canManageInventoryPc())
                        ->before(function ($records): void {
                            $records->each(function (Inventory $record): void {
                                $record->pcComponents()->delete();
                                $record->pcDetail()->delete();
                            });
                        })
                        ->after(function ($records): void {
                            $periods = $records
                                ->map(fn (Inventory $record): string => $record->bulan . '-' . $record->tahun)
                                ->unique();

                            foreach ($periods as $period) {
                                [$bulan, $tahun] = explode('-', $period);

                                InventoryPcIdService::resequence((int) $bulan, (int) $tahun);
                            }
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Informasi Detail')
                ->schema([
                    TextEntry::make('kode_inventaris')
                        ->label('ID')
                        ->formatStateUsing(fn ($state) => $state ? InventoryPcIdService::format((int) $state) : '-'),

                    TextEntry::make('no_pc')
                        ->label('No PC')
                        ->placeholder('-'),

                    TextEntry::make('kode_unique')
                        ->label('Kode BIUM')
                        ->placeholder('-'),

                    TextEntry::make('lokasi.ruang')
                        ->label('Lokasi Terkini')
                        ->placeholder('-'),

                    TextEntry::make('asal.ruang')
                        ->label('Asal')
                        ->placeholder('-'),

                    TextEntry::make('petugas.name')
                        ->label('Petugas')
                        ->placeholder('-'),

                    TextEntry::make('pcDetail.posisi')
                        ->label('Posisi')
                        ->placeholder('-'),
                ])
                ->columns(2),

            InfoSection::make('Spesifikasi Komponen')
                ->description('Daftar komponen yang terpasang pada PC')
                ->schema([
                    RepeatableEntry::make('pcComponents')
                        ->label('')
                        ->schema([
                            TextEntry::make('komponen')
                                ->label('Komponen')
                                ->weight('bold'),

                            TextEntry::make('detail_merk')
                                ->label('Detail')
                                ->placeholder('-'),

                            TextEntry::make('kondisi')
                                ->label('Kondisi')
                                ->badge()
                                ->placeholder('-'),

                            TextEntry::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('-'),
                        ])
                        ->columns(4)
                        ->contained(false),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPCInventories::route('/'),
            'create' => Pages\CreatePCInventory::route('/create'),
        ];
    }

    public static function getActivePeriod(): array
    {
        return [
            'bulan' => (int) (request()->query('bulan') ?: now()->month),
            'tahun' => (int) (request()->query('tahun') ?: now()->year),
        ];
    }

    public static function monthOptions(): array
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

    public static function yearOptions(): array
    {
        $tahunSekarang = (int) now()->year;

        return collect(range($tahunSekarang - 5, $tahunSekarang + 1))
            ->mapWithKeys(fn (int $tahun): array => [$tahun => $tahun])
            ->toArray();
    }

    public static function syncPcComponents(Inventory $inventory, array $components): void
    {
        $rows = [
            ['komponen' => 'Motherboard', 'urutan' => 1, 'motherboard_id' => $components['motherboard_id'] ?? null, 'kondisi' => $components['motherboard_kondisi'] ?? 'Baik', 'keterangan' => $components['motherboard_keterangan'] ?? null],
            ['komponen' => 'Processor', 'urutan' => 2, 'processor_id' => $components['processor_id'] ?? null, 'kondisi' => $components['processor_kondisi'] ?? 'Baik', 'keterangan' => $components['processor_keterangan'] ?? null],
            ['komponen' => 'Hardisk', 'urutan' => 3, 'penyimpanan_id' => $components['penyimpanan_id'] ?? null, 'kondisi' => $components['penyimpanan_kondisi'] ?? 'Baik', 'keterangan' => $components['penyimpanan_keterangan'] ?? null],
            ['komponen' => 'VGA', 'urutan' => 4, 'vga_id' => $components['vga_id'] ?? null, 'kondisi' => $components['vga_kondisi'] ?? 'Baik', 'keterangan' => $components['vga_keterangan'] ?? null],
            ['komponen' => 'RAM', 'urutan' => 5, 'ram_id' => $components['ram_id'] ?? null, 'kondisi' => $components['ram_kondisi'] ?? 'Baik', 'keterangan' => $components['ram_keterangan'] ?? null],
            ['komponen' => 'DVD', 'urutan' => 6, 'dvd_id' => $components['dvd_id'] ?? null, 'kondisi' => $components['dvd_kondisi'] ?? 'Baik', 'keterangan' => $components['dvd_keterangan'] ?? null],
            ['komponen' => 'Keyboard', 'urutan' => 7, 'keyboard_id' => $components['keyboard_id'] ?? null, 'kondisi' => $components['keyboard_kondisi'] ?? 'Baik', 'keterangan' => $components['keyboard_keterangan'] ?? null],
            ['komponen' => 'Mouse', 'urutan' => 8, 'mouse_id' => $components['mouse_id'] ?? null, 'kondisi' => $components['mouse_kondisi'] ?? 'Baik', 'keterangan' => $components['mouse_keterangan'] ?? null],
            ['komponen' => 'Monitor', 'urutan' => 9, 'monitor_id' => $components['monitor_id'] ?? null, 'kondisi' => $components['monitor_kondisi'] ?? 'Baik', 'keterangan' => $components['monitor_keterangan'] ?? null],
        ];

        foreach ($rows as $row) {
            InventoryPcComponent::updateOrCreate(
                ['inventory_id' => $inventory->id, 'komponen' => $row['komponen']],
                array_merge($row, ['inventory_id' => $inventory->id])
            );
        }

        HardwareUsageCounter::recalculate((int) $inventory->bulan, (int) $inventory->tahun);
    }

    public static function movePcToLocation(Inventory $record, int $labTujuanId, int $bulan, int $tahun): void
    {
        $lokasiSebelumnyaId = $record->lokasi_id;
        $lokasiSebelumnya = $lokasiSebelumnyaId ? Laboratorium::find($lokasiSebelumnyaId) : null;
        $labTujuan = Laboratorium::find($labTujuanId);

        $oldIsGudang = static::isGudang($lokasiSebelumnya);
        $newIsGudang = static::isGudang($labTujuan);

        $asalId = match (true) {
            $lokasiSebelumnyaId === null => $labTujuanId,
            $oldIsGudang && ! $newIsGudang => $labTujuanId,
            default => $lokasiSebelumnyaId,
        };

        $record->update([
            'asal_id' => $asalId,
            'lokasi_id' => $labTujuanId,
            'laboratorium_id' => $labTujuanId,
            'petugas_id' => $newIsGudang ? null : static::resolvePetugasForLab($labTujuanId, $bulan, $tahun),
            'no_pc' => static::generateNextNoPc($labTujuanId, $bulan, $tahun, $record->id),
        ]);
    }

    public static function generateNextNoPc(int $labId, int $bulan, int $tahun, ?int $ignoreInventoryId = null): ?string
    {
        $lab = Laboratorium::query()->find($labId);
        $prefix = static::getLabPrefix($lab);

        if (! $prefix) {
            return null;
        }

        $query = Inventory::query()
            ->whereNull('inventoriable_type')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('lokasi_id', $labId)
            ->whereNotNull('no_pc');

        if ($ignoreInventoryId) {
            $query->whereKeyNot($ignoreInventoryId);
        }

        $existingNumbers = $query
            ->pluck('no_pc')
            ->map(fn ($noPc): int => (int) preg_replace('/[^0-9]/', '', strtoupper((string) $noPc)))
            ->filter()
            ->values();

        $nextNumber = ($existingNumbers->max() ?? 0) + 1;

        return $prefix . str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
    }

    public static function getGudang(): ?Laboratorium
    {
        return Laboratorium::query()
            ->where('ruang', 'like', '%Gudang%')
            ->orWhere('ruang', 'like', '%GD%')
            ->orderBy('id')
            ->first();
    }

    protected static function getLabPrefix(?Laboratorium $lab): ?string
    {
        $ruang = strtoupper(trim((string) ($lab?->ruang ?? '')));

        if ($ruang === '') {
            return null;
        }

        if (str_contains($ruang, 'GUDANG') || $ruang === 'GD') {
            return 'GD';
        }

        if (preg_match('/([A-N])$/', $ruang, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected static function isGudang(?Laboratorium $laboratorium): bool
    {
        $ruang = strtoupper((string) ($laboratorium?->ruang ?? ''));

        return str_contains($ruang, 'GUDANG') || $ruang === 'GD';
    }

    protected static function resolvePetugasForLab(int $labId, int $bulan, int $tahun): ?int
    {
        if (Schema::hasTable('user_lab_permissions')) {
            $userId = DB::table('user_lab_permissions')
                ->where('laboratory_id', $labId)
                ->where('is_active', true)
                ->where('tahun', $tahun)
                ->where('bulan_mulai', '<=', $bulan)
                ->where('bulan_selesai', '>=', $bulan)
                ->value('user_id');

            if ($userId) {
                return (int) $userId;
            }
        }

        return User::query()
            ->get()
            ->first(fn (User $user): bool => method_exists($user, 'getAuthorizedLabIds')
                && ! $user->hasAnyRole(['super_admin', 'admin', 'Admin', 'Super Admin'])
                && in_array($labId, $user->getAuthorizedLabIds('view'), true))
            ?->id;
    }

    protected static function conditionOptions(): array
    {
        return [
            'Baik' => 'Baik',
            'Kurang Baik' => 'Kurang Baik',
            'Rusak' => 'Rusak',
            '-' => '-',
        ];
    }

    protected static function componentFields(): array
    {
        return [
            Select::make('components.motherboard_id')
                ->label('Motherboard')
                ->options(fn () => Motherboard::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.motherboard_kondisi')
                ->label('Kondisi')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.motherboard_keterangan')
                ->label('Keterangan')
                ->maxLength(255),

            Select::make('components.processor_id')
                ->label('Processor')
                ->options(fn () => Processor::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.processor_kondisi')
                ->label('Kondisi')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.processor_keterangan')
                ->label('Keterangan')
                ->maxLength(255),

            Select::make('components.penyimpanan_id')
                ->label('Hardisk')
                ->options(fn () => Penyimpanan::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.penyimpanan_kondisi')
                ->label('Kondisi')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.penyimpanan_keterangan')
                ->label('Keterangan')
                ->maxLength(255),

            Select::make('components.vga_id')
                ->label('VGA')
                ->options(fn () => VGA::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.vga_kondisi')
                ->label('Kondisi')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.vga_keterangan')
                ->label('Keterangan')
                ->maxLength(255),

            Select::make('components.ram_id')
                ->label('RAM')
                ->options(fn () => RAM::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.ram_kondisi')
                ->label('Kondisi')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.ram_keterangan')
                ->label('Keterangan')
                ->maxLength(255),

            Select::make('components.dvd_id')
                ->label('DVD')
                ->options(fn () => DVD::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.dvd_kondisi')
                ->label('Kondisi')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.dvd_keterangan')
                ->label('Keterangan')
                ->maxLength(255),

            Select::make('components.keyboard_id')
                ->label('Keyboard')
                ->options(fn () => Keyboard::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.keyboard_kondisi')
                ->label('Kondisi Keyboard')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.keyboard_keterangan')
                ->label('Keterangan Keyboard')
                ->maxLength(255),

            Select::make('components.mouse_id')
                ->label('Mouse')
                ->options(fn () => Mouse::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.mouse_kondisi')
                ->label('Kondisi Mouse')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.mouse_keterangan')
                ->label('Keterangan Mouse')
                ->maxLength(255),

            Select::make('components.monitor_id')
                ->label('Monitor')
                ->options(fn () => Monitor::query()->orderBy('merk')->pluck('merk', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('components.monitor_kondisi')
                ->label('Kondisi')
                ->options(static::conditionOptions())
                ->default('Baik')
                ->required(),

            TextInput::make('components.monitor_keterangan')
                ->label('Keterangan')
                ->maxLength(255),
        ];
    }
}