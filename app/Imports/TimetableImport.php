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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class TimetableImport implements WithMultipleSheets, SkipsUnknownSheets
{
    protected array $importResults = [];
    protected array $errors = [];
    protected array $warnings = [];
    protected array $successCount = [];
    protected bool $previewMode = true;

    public function __construct(bool $previewMode = true)
    {
        $this->previewMode = $previewMode;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Get all active labs and create sheet handlers
        $labs = Laboratorium::where('is_active', true)->get();

        foreach ($labs as $lab) {
            $sheets[$lab->ruang] = new LabScheduleSheetImport($lab, $this->previewMode);
        }

        return $sheets;
    }

    public function onUnknownSheet($sheetName)
    {
        // Skip sheets that don't match lab names (like Sheet1, etc)
        $this->warnings[] = "Sheet '{$sheetName}' tidak dikenali sebagai lab dan diabaikan.";
    }

    /**
     * Get import results from all sheets
     */
    public function getResults(): array
    {
        $results = [];
        $labs = Laboratorium::where('is_active', true)->get();

        foreach ($labs as $lab) {
            // Results will be collected from each sheet handler
        }

        return $this->importResults;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function addResult(string $labName, array $result): void
    {
        if (!isset($this->importResults[$labName])) {
            $this->importResults[$labName] = [];
        }
        $this->importResults[$labName][] = $result;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }
}
