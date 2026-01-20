<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Laboratorium;
use App\Models\Lecturer;
use App\Models\Schedule;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithTitle;

class LabScheduleSheetImport implements ToCollection, WithTitle
{
    protected Laboratorium $lab;
    protected bool $previewMode;
    protected array $results = [];
    protected array $errors = [];

    // Row offset for data (after header rows)
    protected int $headerRows = 4;

    public function __construct(Laboratorium $lab, bool $previewMode = true)
    {
        $this->lab = $lab;
        $this->previewMode = $previewMode;
    }

    public function title(): string
    {
        return $this->lab->ruang;
    }

    /**
     * Process rows from direct PhpSpreadsheet array
     */
    public function processRows(Collection $rows): void
    {
        $this->collection($rows);
    }

    public function collection(Collection $rows)
    {
        $currentDay = null;
        $groupedSchedules = []; // Group by day+course+kelompok+dosen

        foreach ($rows as $index => $row) {
            // Skip header rows (first 4 rows: title, university, address, column headers)
            if ($index < $this->headerRows) {
                continue;
            }

            // Check if this is a blue separator row (empty)
            if ($this->isEmptyRow($row)) {
                continue;
            }

            // Get day from first column (or continue using previous day)
            $dayValue = trim($row[0] ?? '');
            if (!empty($dayValue) && in_array($dayValue, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
                $currentDay = $dayValue;
            }

            // Skip if no valid day
            if (!$currentDay) {
                continue;
            }

            // Parse row data
            $jadwal = trim($row[1] ?? '');
            $mataKuliah = strtoupper(trim($row[2] ?? ''));
            $kelompok = strtoupper(trim($row[3] ?? ''));
            $dosenName = strtoupper(trim($row[4] ?? ''));

            // Skip empty data rows - only import rows with actual schedule data
            // Empty mata_kuliah means the time slot is not filled
            if (empty($jadwal) || empty($mataKuliah)) {
                continue;
            }

            // Parse time
            $times = $this->parseTimeSlot($jadwal);
            if (!$times) {
                continue;
            }

            // Create unique key for grouping (day + course + kelompok + dosen)
            $groupKey = "{$currentDay}|{$mataKuliah}|{$kelompok}|{$dosenName}";

            if (!isset($groupedSchedules[$groupKey])) {
                // First occurrence - create new group
                $groupedSchedules[$groupKey] = [
                    'day' => $currentDay,
                    'mata_kuliah' => $mataKuliah,
                    'kelompok' => $kelompok,
                    'dosen' => $dosenName,
                    'start_time' => $times['start'],
                    'end_time' => $times['end'],
                    'row_number' => $index + 1,
                ];
            } else {
                // Extend end_time to include this slot
                $groupedSchedules[$groupKey]['end_time'] = $times['end'];
            }
        }

        // Process grouped schedules
        foreach ($groupedSchedules as $data) {
            $result = $this->processGroupedRow($data);
            $this->results[] = $result;
        }
    }

    /**
     * Process a grouped schedule row (already merged by time)
     */
    protected function processGroupedRow(array $data): array
    {
        $result = [
            'row' => $data['row_number'],
            'day' => $data['day'],
            'jadwal' => "{$data['start_time']}-{$data['end_time']}",
            'mata_kuliah' => $data['mata_kuliah'],
            'kelompok' => $data['kelompok'],
            'dosen' => $data['dosen'],
            'lab' => $this->lab->ruang,
            'status' => 'pending',
            'errors' => [],
            'warnings' => [],
            'course_id' => null,
            'course_name' => null,
            'lecturer_id' => null,
            'lecturer_name' => null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'create_lecturer' => false,
            'new_lecturer_name' => null,
        ];

        // Find course - if not found, just set to null (warning, not error)
        if (!empty($data['mata_kuliah'])) {
            $courseMatch = $this->findCourse($data['mata_kuliah']);
            if ($courseMatch['found']) {
                $result['course_id'] = $courseMatch['course']->id;
                $result['course_name'] = strtoupper($courseMatch['course']->name);
                if ($courseMatch['fuzzy']) {
                    $result['warnings'][] = "Nama matkul '{$data['mata_kuliah']}' cocok dengan '{$courseMatch['course']->name}'";
                }
            } else {
                $result['warnings'][] = "Matkul tidak ditemukan: {$data['mata_kuliah']} (akan jadi null)";
            }
        }

        // Find or create lecturer
        if (!empty($data['dosen'])) {
            $lecturerMatch = $this->findOrCreateLecturer($data['dosen']);
            if ($lecturerMatch['found']) {
                $result['lecturer_id'] = $lecturerMatch['lecturer']->id;
                $result['lecturer_name'] = strtoupper($lecturerMatch['lecturer']->name);
            } elseif ($lecturerMatch['will_create']) {
                $result['warnings'][] = "Dosen baru akan dibuat: {$data['dosen']}";
                $result['create_lecturer'] = true;
                $result['new_lecturer_name'] = $data['dosen'];
                $result['lecturer_name'] = $data['dosen'];
            }
        }

        // Check for duplicates
        if ($result['start_time']) {
            $duplicate = $this->checkDuplicate($result);
            if ($duplicate) {
                $result['errors'][] = "Jadwal duplikat: sudah ada jadwal untuk waktu ini";
            }
        }

        // Set final status
        if (!empty($result['errors'])) {
            $result['status'] = 'error';
        } elseif (!empty($result['warnings'])) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'valid';
        }

        return $result;
    }

    protected function isEmptyRow($row): bool
    {
        foreach ($row as $cell) {
            if (!empty(trim($cell ?? ''))) {
                return false;
            }
        }
        return true;
    }

    protected function processRow(array $data): array
    {
        // Ensure UPPERCASE for all text data
        $mataKuliah = strtoupper(trim($data['mata_kuliah']));
        $kelompok = strtoupper(trim($data['kelompok']));
        $dosen = strtoupper(trim($data['dosen']));

        $result = [
            'row' => $data['row_number'],
            'day' => $data['day'],
            'jadwal' => $data['jadwal'],
            'mata_kuliah' => $mataKuliah,
            'kelompok' => $kelompok,
            'dosen' => $dosen,
            'lab' => $this->lab->ruang,
            'status' => 'pending',
            'errors' => [],
            'warnings' => [],
            'course_id' => null,
            'course_name' => null,
            'lecturer_id' => null,
            'lecturer_name' => null,
            'start_time' => null,
            'end_time' => null,
            'create_lecturer' => false,
            'new_lecturer_name' => null,
        ];

        // Parse time
        $times = $this->parseTimeSlot($data['jadwal']);
        if ($times) {
            $result['start_time'] = $times['start'];
            $result['end_time'] = $times['end'];
        } else {
            $result['errors'][] = "Format waktu tidak valid: {$data['jadwal']}";
        }

        // Find course - if not found, just set to null (warning, not error)
        $courseMatch = $this->findCourse($mataKuliah);
        if ($courseMatch['found']) {
            $result['course_id'] = $courseMatch['course']->id;
            $result['course_name'] = strtoupper($courseMatch['course']->name);
            if ($courseMatch['fuzzy']) {
                $result['warnings'][] = "Nama matkul '{$mataKuliah}' cocok dengan '{$courseMatch['course']->name}'";
            }
        } else {
            // Course not found - treat as warning, not error. Will be null.
            $result['warnings'][] = "Matkul tidak ditemukan: {$mataKuliah} (akan jadi null)";
        }

        // Find or create lecturer
        if (!empty($dosen)) {
            $lecturerMatch = $this->findOrCreateLecturer($dosen);
            if ($lecturerMatch['found']) {
                $result['lecturer_id'] = $lecturerMatch['lecturer']->id;
                $result['lecturer_name'] = strtoupper($lecturerMatch['lecturer']->name);
            } elseif ($lecturerMatch['will_create']) {
                $result['warnings'][] = "Dosen baru akan dibuat: {$dosen}";
                $result['create_lecturer'] = true;
                $result['new_lecturer_name'] = $dosen;
                $result['lecturer_name'] = $dosen;
            }
        }

        // Check for duplicates (only if we have time)
        if ($result['start_time']) {
            $duplicate = $this->checkDuplicate($result);
            if ($duplicate) {
                $result['errors'][] = "Jadwal duplikat: sudah ada jadwal untuk waktu ini";
            }
        }

        // Set final status - only error if time is invalid or duplicate
        if (!empty($result['errors'])) {
            $result['status'] = 'error';
        } elseif (!empty($result['warnings'])) {
            $result['status'] = 'warning';
        } else {
            $result['status'] = 'valid';
        }

        return $result;
    }

    protected function parseTimeSlot(string $jadwal): ?array
    {
        // Format: "07:00-07:50" or "07.00-07.50"
        $jadwal = str_replace('.', ':', $jadwal);

        if (preg_match('/(\d{2}:\d{2})-(\d{2}:\d{2})/', $jadwal, $matches)) {
            return [
                'start' => $matches[1],
                'end' => $matches[2],
            ];
        }

        return null;
    }

    protected function findCourse(string $name): array
    {
        $name = strtoupper(trim($name));

        // Exact match
        $course = Course::whereRaw('UPPER(name) = ?', [$name])->first();
        if ($course) {
            return ['found' => true, 'course' => $course, 'fuzzy' => false];
        }

        // Fuzzy match (contains)
        $course = Course::whereRaw('UPPER(name) LIKE ?', ["%{$name}%"])->first();
        if ($course) {
            return ['found' => true, 'course' => $course, 'fuzzy' => true];
        }

        // Get suggestions
        $suggestions = Course::whereRaw('UPPER(name) LIKE ?', ["%" . substr($name, 0, 5) . "%"])
            ->limit(5)
            ->get();

        return ['found' => false, 'suggestions' => $suggestions];
    }

    protected function findOrCreateLecturer(string $name): array
    {
        if (empty($name)) {
            return ['found' => false, 'will_create' => false];
        }

        $name = strtoupper(trim($name));

        // Exact match
        $lecturer = Lecturer::whereRaw('UPPER(name) = ?', [$name])->first();
        if ($lecturer) {
            return ['found' => true, 'lecturer' => $lecturer, 'will_create' => false];
        }

        // Fuzzy match
        $lecturer = Lecturer::whereRaw('UPPER(name) LIKE ?', ["%{$name}%"])->first();
        if ($lecturer) {
            return ['found' => true, 'lecturer' => $lecturer, 'will_create' => false];
        }

        // Will create new
        return ['found' => false, 'will_create' => true, 'new_name' => $name];
    }

    protected function checkDuplicate(array $result): bool
    {
        // Check for overlapping schedules on the same day in the same lab
        // Overlap occurs when: existing.start < new.end AND existing.end > new.start
        $newStart = Carbon::createFromFormat('H:i', $result['start_time']);
        $newEnd = Carbon::createFromFormat('H:i', $result['end_time']);

        $existingSchedules = Schedule::where('laboratorium_id', $this->lab->id)
            ->where('day', $result['day'])
            ->get();

        foreach ($existingSchedules as $schedule) {
            $existStart = Carbon::parse($schedule->start_time);
            $existEnd = Carbon::parse($schedule->end_time);

            // Check overlap: max(start1, start2) < min(end1, end2)
            if ($existStart->lt($newEnd) && $existEnd->gt($newStart)) {
                return true; // Overlap found
            }
        }

        return false;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Actually import the valid rows
     */
    public function doImport(): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($this->results as $result) {
            if ($result['status'] === 'error') {
                $skipped++;
                continue;
            }

            // Create lecturer if needed
            $lecturerId = $result['lecturer_id'];
            if (!empty($result['create_lecturer']) && !empty($result['new_lecturer_name'])) {
                $lecturer = Lecturer::create(['name' => $result['new_lecturer_name']]);
                $lecturerId = $lecturer->id;
            }

            // Create schedule
            if ($result['course_id'] && $result['start_time']) {
                Schedule::create([
                    'course_id' => $result['course_id'],
                    'lecturer_id' => $lecturerId,
                    'laboratorium_id' => $this->lab->id,
                    'day' => $result['day'],
                    'start_time' => $result['start_time'],
                    'end_time' => $result['end_time'],
                    'kelompok' => $result['kelompok'],
                ]);
                $imported++;
            } else {
                $skipped++;
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }
}
