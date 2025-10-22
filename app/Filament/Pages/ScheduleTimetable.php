<?php

namespace App\Filament\Pages;

use App\Models\Schedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Laboratorium;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class ScheduleTimetable extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Penjadwalan';

    protected static ?string $navigationLabel = 'Timetable Visual';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.schedule-timetable';

    protected static ?string $title = 'Timetable Visual';

    public ?int $selectedLabId = null;
    public array $schedulesByTimeSlotAndDay = [];

    public function mount(): void
    {
        $firstLab = Laboratorium::first();
        if ($firstLab) {
            $this->selectedLabId = $firstLab->id;
            $this->loadSchedules();
        }
    }

    public function updatedSelectedLabId(): void
    {
        $this->loadSchedules();
    }

    /**
     * Menghasilkan array slot waktu per 50 menit dari 07:00 hingga 21:00
     */
    public function getTimeSlots(): array
    {
        $slots = [];
        $start = Carbon::createFromTime(7, 0);
        $end = Carbon::createFromTime(21, 0);

        while ($start->lessThan($end)) {
            $slotEnd = $start->copy()->addMinutes(50);
            $slots[] = $start->format('H:i');
            $start->addMinutes(50);
        }

        return $slots;
    }

    /**
     * Memuat jadwal dari database dan mengisi schedulesByTimeSlotAndDay
     */
    public function loadSchedules(): void
    {
        $this->schedulesByTimeSlotAndDay = [];

        if (!$this->selectedLabId) {
            return;
        }

        // Inisialisasi array kosong untuk semua slot waktu dan hari
        $timeSlots = $this->getTimeSlots();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        foreach ($timeSlots as $timeSlot) {
            foreach ($days as $day) {
                $this->schedulesByTimeSlotAndDay[$timeSlot][$day] = null;
            }
        }

        // Ambil semua jadwal untuk laboratorium yang dipilih dengan eager loading
        $schedules = Schedule::with(['course', 'lecturer'])
            ->where('laboratorium_id', $this->selectedLabId)
            ->get();

        // Loop melalui setiap jadwal dan isi slot waktu yang sesuai
        foreach ($schedules as $schedule) {
            // Parse datetime dari database dan ambil bagian time saja
            $startTime = Carbon::parse($schedule->start_time);
            $endTime = Carbon::parse($schedule->end_time);

            // Loop melalui setiap slot 50 menit yang ter-cover oleh jadwal ini
            $currentTime = $startTime->copy();
            while ($currentTime->lessThan($endTime)) {
                $timeKey = $currentTime->format('H:i');

                // Masukkan objek schedule yang sama ke setiap slot yang ter-cover
                if (isset($this->schedulesByTimeSlotAndDay[$timeKey][$schedule->day])) {
                    $this->schedulesByTimeSlotAndDay[$timeKey][$schedule->day] = $schedule;
                }

                $currentTime->addMinutes(50);
            }
        }

        // Debug: Log hasil loading untuk troubleshooting
        \Log::info('Schedules loaded for lab ' . $this->selectedLabId, [
            'total_schedules' => $schedules->count(),
            'filled_slots' => array_sum(array_map(function($day) {
                return count(array_filter($day, function($slot) { return $slot !== null; }));
            }, $this->schedulesByTimeSlotAndDay))
        ]);
    }

    /**
     * Mendefinisikan schema form untuk create/edit jadwal
     */
    public function getFormSchema(?string $day = null, ?string $startTime = null): array
    {
        return [
            Forms\Components\Section::make('Informasi Dasar')
                ->schema([
                    Forms\Components\Hidden::make('laboratorium_id')
                        ->default(fn () => $this->selectedLabId),

                    Forms\Components\Select::make('course_id')
                        ->label('Mata Kuliah')
                        ->options(Course::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state) {
                                $course = Course::find($state);
                                $startTimeValue = $get('start_time');
                                if ($course && $startTimeValue) {
                                    $endTime = $this->calculateEndTime($startTimeValue, $course->sks);
                                    $set('end_time', $endTime);
                                }
                            }
                        })
                        ->helperText('Pilih mata kuliah'),

                    Forms\Components\TextInput::make('kelompok')
                        ->label('Kelompok')
                        ->maxLength(255)
                        ->helperText('Contoh: A, B, C, atau kosongkan jika tidak ada kelompok'),

                    Forms\Components\Select::make('lecturer_id')
                        ->label('Dosen')
                        ->options(Lecturer::pluck('name', 'id'))
                        ->searchable()
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
                        ->default($day)
                        ->required()
                        ->helperText('Pilih hari perkuliahan'),

                    Forms\Components\TimePicker::make('start_time')
                        ->label('Jam Mulai')
                        ->default($startTime)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state) {
                                $courseId = $get('course_id');
                                if ($courseId) {
                                    $course = Course::find($courseId);
                                    if ($course) {
                                        $endTime = $this->calculateEndTime($state, $course->sks);
                                        $set('end_time', $endTime);
                                    }
                                }
                            }
                        })
                        ->helperText('Jam mulai perkuliahan'),

                    Forms\Components\TimePicker::make('end_time')
                        ->label('Jam Selesai')
                        ->disabled()
                        ->dehydrated() // Pastikan field ini ikut di-submit meskipun disabled
                        ->helperText('Otomatis dihitung berdasarkan SKS mata kuliah'),
                ])
                ->columns(3),
        ];
    }

    /**
     * Helper function untuk menghitung end_time
     */
    private function calculateEndTime(?string $startTime, ?int $sks): ?string
    {
        if (empty($startTime) || empty($sks) || $sks <= 0) {
            return null;
        }

        try {
            // Bersihkan format waktu dan pastikan formatnya konsisten
            $startTime = trim($startTime);

            // Jika sudah dalam format HH:MM, gunakan langsung
            if (preg_match('/^\d{1,2}:\d{2}$/', $startTime)) {
                $timeParts = explode(':', $startTime);
                $hours = (int)$timeParts[0];
                $minutes = (int)$timeParts[1];
            }
            // Jika dalam format HH:MM:SS, ambil jam dan menit saja
            elseif (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $startTime)) {
                $timeParts = explode(':', $startTime);
                $hours = (int)$timeParts[0];
                $minutes = (int)$timeParts[1];
            }
            else {
                return null;
            }

            // Validasi waktu
            if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
                return null;
            }

            // Hitung total menit dari jam mulai
            $totalStartMinutes = ($hours * 60) + $minutes;

            // Tambahkan durasi berdasarkan SKS (1 SKS = 50 menit)
            $durationMinutes = $sks * 50;
            $totalEndMinutes = $totalStartMinutes + $durationMinutes;

            // Konversi kembali ke jam dan menit
            $endHours = intval($totalEndMinutes / 60);
            $endMinutes = $totalEndMinutes % 60;

            // Format output dengan padding zero
            return sprintf('%02d:%02d', $endHours, $endMinutes);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Action untuk membuat jadwal baru
     */
    public function createAction(): Action
    {
        return Action::make('create')
            ->label('Tambah Jadwal')
            ->icon('heroicon-o-plus')
            ->form(function (array $arguments) {
                $day = $arguments['day'] ?? null;
                $time = $arguments['time'] ?? null;
                return $this->getFormSchema($day, $time);
            })
            ->action(function (array $data): void {
                // Validasi dan pastikan semua field yang diperlukan terisi
                if (empty($data['course_id'])) {
                    Notification::make()
                        ->title('Error: Mata kuliah harus dipilih')
                        ->danger()
                        ->send();
                    return;
                }

                if (empty($data['start_time'])) {
                    Notification::make()
                        ->title('Error: Jam mulai harus diisi')
                        ->danger()
                        ->send();
                    return;
                }

                // Pastikan end_time selalu terisi berdasarkan course dan start_time
                $course = Course::find($data['course_id']);
                if (!$course) {
                    Notification::make()
                        ->title('Error: Mata kuliah tidak ditemukan')
                        ->danger()
                        ->send();
                    return;
                }

                // Force calculate end_time
                \Log::info('Attempting to calculate end_time', [
                    'start_time' => $data['start_time'],
                    'course_sks' => $course->sks,
                    'course_id' => $course->id,
                    'course_name' => $course->name
                ]);

                $data['end_time'] = $this->calculateEndTime($data['start_time'], $course->sks);

                \Log::info('End time calculation result', [
                    'calculated_end_time' => $data['end_time'],
                    'is_empty' => empty($data['end_time'])
                ]);

                if (empty($data['end_time'])) {
                    Notification::make()
                        ->title('Error: Gagal menghitung jam selesai')
                        ->body('Start Time: ' . $data['start_time'] . ', SKS: ' . $course->sks)
                        ->danger()
                        ->send();
                    return;
                }

                // Pastikan laboratorium_id terisi
                $data['laboratorium_id'] = $this->selectedLabId;

                // Cek apakah ada bentrok jadwal
                $existingSchedule = Schedule::where('laboratorium_id', $this->selectedLabId)
                    ->where('day', $data['day'])
                    ->where(function($query) use ($data) {
                        $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                              ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                              ->orWhere(function($q) use ($data) {
                                  $q->where('start_time', '<=', $data['start_time'])
                                    ->where('end_time', '>=', $data['end_time']);
                              });
                    })
                    ->first();

                if ($existingSchedule) {
                    Notification::make()
                        ->title('Error: Jadwal bentrok')
                        ->body('Sudah ada jadwal lain pada waktu tersebut')
                        ->danger()
                        ->send();
                    return;
                }

                try {
                    Schedule::create($data);

                    Notification::make()
                        ->title('Jadwal berhasil ditambahkan')
                        ->success()
                        ->send();

                    $this->loadSchedules();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error: Gagal menyimpan jadwal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Action untuk mengedit jadwal
     */
    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit Jadwal')
            ->icon('heroicon-o-pencil')
            ->record(function (array $arguments) {
                return Schedule::find($arguments['scheduleId']);
            })
            ->form($this->getFormSchema())
            ->fillForm(function (Schedule $record): array {
                return $record->toArray();
            })
            ->action(function (array $data, Schedule $record): void {
                // Pastikan end_time terisi berdasarkan course dan start_time
                if (empty($data['end_time']) && !empty($data['course_id']) && !empty($data['start_time'])) {
                    $course = Course::find($data['course_id']);
                    if ($course) {
                        $data['end_time'] = $this->calculateEndTime($data['start_time'], $course->sks);
                    }
                }

                $data['laboratorium_id'] = $this->selectedLabId;
                $record->update($data);

                Notification::make()
                    ->title('Jadwal berhasil diperbarui')
                    ->success()
                    ->send();

                $this->loadSchedules();
            })
            ->modalActions([
                Forms\Components\Actions\Action::make('delete')
                    ->label('Hapus Jadwal')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Schedule $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Jadwal berhasil dihapus')
                            ->success()
                            ->send();

                        $this->loadSchedules();
                    }),
            ]);
    }

    /**
     * Action untuk menghapus jadwal
     */
    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Hapus Jadwal')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->record(function (array $arguments) {
                return Schedule::find($arguments['scheduleId']);
            })
            ->action(function (Schedule $record): void {
                $record->delete();

                Notification::make()
                    ->title('Jadwal berhasil dihapus')
                    ->success()
                    ->send();

                $this->loadSchedules();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createAction(),
        ];
    }
}
