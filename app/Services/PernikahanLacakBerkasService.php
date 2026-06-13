<?php

namespace App\Services;

use App\Models\Antrian_Online_Model;
use App\Models\Lacak_Berkas_Model;
use App\Models\LayananPernikahan;
use App\Models\StatusPerkawinanHistory;
use Illuminate\Support\Facades\Log;

/**
 * Sinkronisasi riwayat lacak_berkas untuk layanan pernikahan.
 * Pola sama dengan layanan lain: setiap perubahan status menulis ke lacak_berkas
 * yang terhubung ke antrian_online via nomor_antrian.
 */
class PernikahanLacakBerkasService
{
    /** @var array<string, array{status: string, keterangan: string}> */
    public const STATUS_LACAK_MAP = [
        LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN => [
            'status' => 'Konfirmasi Keagamaan',
            'keterangan' => 'Permohonan pernikahan diterima. Menunggu konfirmasi dari petugas keagamaan.',
        ],
        LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN => [
            'status' => 'Ditolak',
            'keterangan' => 'Permohonan pernikahan ditolak oleh petugas keagamaan.',
        ],
        LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL => [
            'status' => 'Persetujuan Tanggal',
            'keterangan' => 'Konfirmasi keagamaan selesai. Menunggu persetujuan tanggal perkawinan oleh admin.',
        ],
        LayananPernikahan::STATUS_TANGGAL_DITOLAK => [
            'status' => 'Tanggal Ditolak',
            'keterangan' => 'Tanggal perkawinan ditolak oleh admin. Menunggu penyesuaian.',
        ],
        LayananPernikahan::STATUS_TANGGAL_DISETUJUI => [
            'status' => 'Tanggal Disetujui',
            'keterangan' => 'Tanggal perkawinan disetujui. Silakan upload dokumen persyaratan.',
        ],
        LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI => [
            'status' => 'Verifikasi Dokumen',
            'keterangan' => 'Dokumen telah diupload. Menunggu verifikasi oleh admin.',
        ],
        LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN => [
            'status' => 'Dokumen Perlu Perbaikan',
            'keterangan' => 'Dokumen perlu diperbaiki. Silakan upload ulang dokumen yang diminta.',
        ],
        LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI => [
            'status' => 'Dokumen Diverifikasi',
            'keterangan' => 'Semua dokumen telah diverifikasi. Proses penerbitan dokumen resmi.',
        ],
        LayananPernikahan::STATUS_SELESAI => [
            'status' => 'Selesai',
            'keterangan' => 'Layanan pernikahan selesai. Dokumen resmi telah diterbitkan.',
        ],
    ];

    /**
     * Catat status pernikahan ke lacak_berkas jika antrian_online ditemukan.
     */
    public static function recordStatus(LayananPernikahan $pernikahan, ?string $statusOverride = null): void
    {
        if (empty($pernikahan->nomor_antrian)) {
            return;
        }

        $antrian = Antrian_Online_Model::query()
            ->where('nomor_antrian', $pernikahan->nomor_antrian)
            ->first();

        if (! $antrian) {
            return;
        }

        $workflowStatus = $statusOverride ?? $pernikahan->status;
        $mapped = self::STATUS_LACAK_MAP[$workflowStatus] ?? null;

        if (! $mapped) {
            return;
        }

        $lacakStatus = $mapped['status'];
        $keterangan = $mapped['keterangan'];

        $latest = Lacak_Berkas_Model::query()
            ->where('antrian_online_id', $antrian->antrian_online_id)
            ->orderByDesc('created_at')
            ->first();

        if ($latest && $latest->status === $lacakStatus) {
            return;
        }

        $alasanPenolakan = null;
        if (in_array($workflowStatus, [
            LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
            LayananPernikahan::STATUS_TANGGAL_DITOLAK,
        ], true)) {
            $alasanPenolakan = $pernikahan->alasan_ditolak ?: $pernikahan->catatan_keagamaan;
        }

        Lacak_Berkas_Model::create([
            'antrian_online_id' => $antrian->antrian_online_id,
            'status' => $lacakStatus,
            'tanggal' => now()->toDateString(),
            'keterangan' => $keterangan,
            'alasan_penolakan' => $alasanPenolakan,
        ]);

        $antrianStatus = self::mapToAntrianStatus($workflowStatus);
        if ($antrianStatus && $antrian->status_antrian !== $antrianStatus) {
            $antrian->update(['status_antrian' => $antrianStatus]);
        }
    }

    /**
     * Backfill lacak_berkas dari riwayat status_perkawinan_history untuk data lama.
     */
    public static function backfillFromHistory(LayananPernikahan $pernikahan): void
    {
        if (empty($pernikahan->nomor_antrian)) {
            return;
        }

        $antrian = Antrian_Online_Model::query()
            ->where('nomor_antrian', $pernikahan->nomor_antrian)
            ->first();

        if (! $antrian) {
            return;
        }

        $existingCount = Lacak_Berkas_Model::query()
            ->where('antrian_online_id', $antrian->antrian_online_id)
            ->count();

        if ($existingCount > 0) {
            return;
        }

        try {
            $histories = StatusPerkawinanHistory::query()
                ->where('pernikahan_id', $pernikahan->pernikahan_id)
                ->orderBy('created_at')
                ->get();
        } catch (\Throwable $e) {
            Log::warning('PernikahanLacakBerkasService backfill skipped', [
                'pernikahan_id' => $pernikahan->pernikahan_id,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        if ($histories->isEmpty()) {
            self::recordStatus($pernikahan);

            return;
        }

        foreach ($histories as $history) {
            $mapped = self::STATUS_LACAK_MAP[$history->status_setelah] ?? null;
            if (! $mapped) {
                continue;
            }

            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrian->antrian_online_id,
                'status' => $mapped['status'],
                'tanggal' => $history->created_at?->toDateString() ?? now()->toDateString(),
                'keterangan' => $history->catatan ?: $mapped['keterangan'],
                'created_at' => $history->created_at ?? now(),
                'updated_at' => $history->created_at ?? now(),
            ]);
        }

        self::recordStatus($pernikahan);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function buildRiwayat(LayananPernikahan $pernikahan): array
    {
        self::backfillFromHistory($pernikahan);

        if (empty($pernikahan->nomor_antrian)) {
            return [];
        }

        $antrian = Antrian_Online_Model::query()
            ->where('nomor_antrian', $pernikahan->nomor_antrian)
            ->first();

        if (! $antrian) {
            return [];
        }

        return Lacak_Berkas_Model::query()
            ->where('antrian_online_id', $antrian->antrian_online_id)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($lb) => [
                'status' => $lb->status,
                'tanggal' => $lb->tanggal ?: ($lb->created_at?->format('d M Y') ?? date('d M Y')),
                'keterangan' => $lb->keterangan,
                'alasan_penolakan' => $lb->alasan_penolakan,
                'created_at' => $lb->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    private static function mapToAntrianStatus(string $pernikahanStatus): ?string
    {
        return match ($pernikahanStatus) {
            LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN => 'Menunggu',
            LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
            LayananPernikahan::STATUS_TANGGAL_DITOLAK => 'Ditolak',
            LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
            LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
            LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
            LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN => 'Verifikasi Data',
            LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI => 'Proses Cetak',
            LayananPernikahan::STATUS_SELESAI => 'Selesai',
            default => null,
        };
    }
}
