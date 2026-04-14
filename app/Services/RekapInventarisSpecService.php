<?php

namespace App\Services;

use App\Models\RekapInventarisPeriode;
use App\Models\RekapInventarisPc;
use App\Models\RekapInventarisSpec;
use App\Models\RekapInventarisSpecDetail;
use Illuminate\Support\Facades\DB;

class RekapInventarisSpecService
{
    public const KOMPONEN = [
        1 => "Motherboard",
        2 => "Processor",
        3 => "Hardisk",
        4 => "VGA",
        5 => "RAM",
        6 => "DVD",
        7 => "Keyboard",
        8 => "Mouse",
        9 => "Monitor",
    ];

    public function findOrCreate(int $periodeId, array $details, string $kondisiPc): RekapInventarisSpec
    {
        $normalizedForStore = $this->normalizeDetailsForStore($details);
        $fingerprint = $this->makeFingerprint($details, $kondisiPc);

        $existing = RekapInventarisSpec::query()
            ->where("rekap_inventaris_periode_id", $periodeId)
            ->where("fingerprint", $fingerprint)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($periodeId, $normalizedForStore, $fingerprint, $kondisiPc) {
            $periode = RekapInventarisPeriode::findOrFail($periodeId);

            $spec = RekapInventarisSpec::create([
                "rekap_inventaris_periode_id" => $periodeId,
                "kode_spek" => "TMP-" . $periode->tahun . "-" . uniqid(),
                "urutan_kode" => 9999,
                "fingerprint" => $fingerprint,
                "kondisi_pc" => $kondisiPc,
            ]);

            foreach ($normalizedForStore as $index => $row) {
                RekapInventarisSpecDetail::create([
                    "rekap_inventaris_spec_id" => $spec->id,
                    "komponen" => $row["komponen"],
                    "detail" => $row["detail"],
                    "kondisi" => $row["kondisi"],
                    "catatan_kondisi" => $row["catatan_kondisi"],
                    "urutan" => $index + 1,
                ]);
            }

            return $spec;
        });
    }

    public function fingerprintFromDetails(array $details, string $kondisiPc): string
    {
        return $this->makeFingerprint($details, $kondisiPc);
    }

    protected function makeFingerprint(array $details, string $kondisiPc): string
    {
        $normalized = [
            "kondisi_pc" => $kondisiPc,
            "details" => [],
        ];

        foreach (self::KOMPONEN as $index => $komponen) {
            $normalized["details"][] = [
                "komponen" => $komponen,
                "detail" => trim((string) ($details[$index]["detail"] ?? "")),
                "kondisi" => $details[$index]["kondisi"] ?? null,
            ];
        }

        return md5(json_encode($normalized));
    }

    protected function normalizeDetailsForStore(array $details): array
    {
        $result = [];

        foreach (self::KOMPONEN as $index => $komponen) {
            $result[] = [
                "komponen" => $komponen,
                "detail" => trim((string) ($details[$index]["detail"] ?? "")),
                "kondisi" => $details[$index]["kondisi"] ?? null,
                "catatan_kondisi" => trim((string) ($details[$index]["catatan_kondisi"] ?? "")),
            ];
        }

        return $result;
    }

    public function syncPeriodSpecOrder(int $periodeId): void
    {
        // Fetch periode ONCE outside the loop to avoid N+1 queries
        $periode = RekapInventarisPeriode::find($periodeId);
        $tahun = $periode ? $periode->tahun : date('Y');

        // Get all specs for this period ordered by usage (most used first)
        $specs = RekapInventarisSpec::query()
            ->where('rekap_inventaris_periode_id', $periodeId)
            ->withCount('pcs')
            ->orderBy('pcs_count', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Batch all updates inside a single transaction for speed
        DB::transaction(function () use ($specs, $tahun) {
            $order = 1;
            foreach ($specs as $spec) {
                $spec->update([
                    'urutan_kode' => $order,
                    'kode_spek' => "SPK-{$tahun}-" . str_pad($order, 3, '0', STR_PAD_LEFT),
                ]);
                $order++;
            }
        });
    }
}
