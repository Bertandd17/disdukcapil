<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class KtpOcrExtractionService
{
    private const VALID_PROVINCE_CODES = [
        '11', '12', '13', '14', '15', '16', '17', '18', '19', '21',
        '31', '32', '33', '34', '35', '36',
        '51', '52', '53',
        '61', '62', '63', '64', '65',
        '71', '72', '73', '74', '75', '76',
        '81', '82',
        '91', '92', '94',
    ];

    private const NOISE_KEYWORDS = [
        'PROVINSI', 'KOTA', 'KABUPATEN', 'JAKARTA', 'NIK', 'TEMPAT', 'TGL LAHIR',
        'TANGGAL LAHIR', 'JENIS KELAMIN', 'GOL DARAH', 'GOL. DARAH', 'ALAMAT',
        'RT/RW', 'KEL/DESA', 'KELURAHAN', 'DESA', 'KECAMATAN', 'AGAMA',
        'STATUS PERKAWINAN', 'PEKERJAAN', 'KEWARGANEGARAAN', 'BERLAKU HINGGA',
    ];

    private const NIK_PATTERN = '/\b\d{16}\b/';
    private const DATE_PATTERN = '/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/';
    private const RT_RW_PATTERN = '/(?:RT.?\/RW.?|RT\.\s*\/\s*RW\.?)\s*[:\.]?\s*(\d{1,3})\s*\/\s*(\d{1,3})/iu';

    public function extract(string $rawText, ?string $filePath = null): array
    {
        $lines = $this->splitLines($rawText);

        Log::debug('KtpOcrExtractionService: processing', [
            'line_count' => count($lines),
            'text_length' => strlen($rawText),
            'file_path' => $filePath,
        ]);

        $nik = $this->extractNik($lines);
        $namaLengkap = $this->extractNamaLengkap($lines);
        $tempatLahir = $this->extractTempatLahir($lines);
        $tanggalLahir = $this->extractTanggalLahir($lines);
        $alamat = $this->extractAlamat($lines);
        $rtRw = $this->extractRtRw($lines);
        $kelDesa = $this->extractKelDesa($lines);
        $kecamatan = $this->extractKecamatan($lines);
        $agama = $this->extractAgama($lines);
        $statusPerkawinan = $this->extractStatusPerkawinan($lines);

        $fieldConfidences = [
            'nik' => $nik['confidence'],
            'nama_lengkap' => $namaLengkap['confidence'],
            'tempat_lahir' => $tempatLahir['confidence'],
            'tanggal_lahir' => $tanggalLahir['confidence'],
            'alamat' => $alamat['confidence'],
            'rt_rw' => $rtRw['confidence'],
            'kel_desa' => $kelDesa['confidence'],
            'kecamatan' => $kecamatan['confidence'],
            'agama' => $agama['confidence'],
            'status_perkawinan' => $statusPerkawinan['confidence'],
        ];

        $overallConfidence = $this->calculateOverallConfidence($fieldConfidences);

        return [
            'nik' => $nik['value'],
            'nama_lengkap' => $namaLengkap['value'],
            'tempat_lahir' => $tempatLahir['value'],
            'tanggal_lahir' => $tanggalLahir['value'],
            'alamat' => $alamat['value'],
            'rt_rw' => $rtRw['value'],
            'kel_desa' => $kelDesa['value'],
            'kecamatan' => $kecamatan['value'],
            'agama' => $agama['value'],
            'status_perkawinan' => $statusPerkawinan['value'],
            'confidence' => round($overallConfidence, 4),
            'field_confidence' => $fieldConfidences,
            'needs_manual_review' => $overallConfidence < 0.7,
        ];
    }

    private function splitLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);

        return array_values(array_filter(
            array_map(fn($line) => trim($line), $lines),
            fn($line) => $line !== ''
        ));
    }

    private function normalizeDigits(string $value): string
    {
        $cleaned = preg_replace('/\s+/', '', $value);
        $cleaned = str_replace(['O', 'o'], '0', $cleaned);
        $cleaned = str_replace(['I', 'l'], '1', $cleaned);
        $cleaned = preg_replace('/\D/', '', $cleaned);

        return $cleaned;
    }

    private function cleanValue(string $value): string
    {
        $value = preg_replace('/\s+/', ' ', $value);
        $value = str_replace([':', '.', '-', ','], ' ', $value);

        return trim($value, " \t\n\r\0\x0B:.-,");
    }

    private function looksLikeLabel(string $line): bool
    {
        $upper = mb_strtoupper($line);

        foreach (self::NOISE_KEYWORDS as $keyword) {
            if (mb_strpos($upper, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function extractNik(array $lines): array
    {
        $candidate = '';

        foreach ($lines as $line) {
            if (preg_match('/(?:NIK|N\s*I\s*K)\s*[:\.]?\s*([0-9OoIl\s]{14,22})/iu', $line, $matches)) {
                $candidate = $this->normalizeDigits($matches[1]);
                if (strlen($candidate) === 16) {
                    break;
                }
            }
        }

        if (strlen($candidate) !== 16) {
            $joined = implode(' ', $lines);
            if (preg_match(self::NIK_PATTERN, $joined, $matches)) {
                $candidate = $matches[0];
            }
        }

        if (strlen($candidate) !== 16) {
            return ['value' => '', 'confidence' => 0.0];
        }

        $provinceCode = substr($candidate, 0, 2);
        $isValidProvince = in_array($provinceCode, self::VALID_PROVINCE_CODES, true);
        $checksumValid = $this->validateNikChecksum($candidate);

        if ($isValidProvince && $checksumValid) {
            return ['value' => $candidate, 'confidence' => 1.0];
        } elseif ($isValidProvince) {
            return ['value' => $candidate, 'confidence' => 0.9];
        } else {
            return ['value' => $candidate, 'confidence' => 0.7];
        }
    }

    private function validateNikChecksum(string $nik): bool
    {
        if (strlen($nik) !== 16) {
            return false;
        }

        $weights = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18];
        $sum = 0;

        for ($i = 0; $i < 15; $i++) {
            $sum += (int)$nik[$i] * $weights[$i];
        }

        $mod = $sum % 11;
        $checkDigit = (11 - $mod) % 11;

        return $checkDigit === (int)$nik[15];
    }

    private function extractNamaLengkap(array $lines): array
    {
        $nama = '';

        foreach ($lines as $idx => $line) {
            if (preg_match('/(?:Nama|NAMA|N\s*a\s*m\s*a)\s*[:\.]?\s*(.+)/iu', $line, $matches)) {
                $candidate = $this->cleanValue($matches[1]);

                if ($candidate === '' && isset($lines[$idx + 1])) {
                    $candidate = $this->cleanValue($lines[$idx + 1]);
                }

                if ($candidate !== '' && !$this->looksLikeLabel($candidate)) {
                    $nama = $candidate;
                    break;
                }
            }
        }

        if ($nama === '') {
            foreach ($lines as $line) {
                $stripped = $this->cleanValue($line);

                if (
                    $stripped !== ''
                    && $stripped === mb_strtoupper($stripped)
                    && !$this->looksLikeLabel($stripped)
                    && mb_strlen($stripped) >= 3
                    && mb_strlen($stripped) <= 60
                    && preg_match("/^[A-Z\s'\.,\-]+$/u", $stripped)
                ) {
                    $nama = $stripped;
                    break;
                }
            }
        }

        if ($nama === '') {
            return ['value' => '', 'confidence' => 0.0];
        }

        $nama = mb_strtoupper($nama);

        if (mb_strlen($nama) >= 3 && mb_strlen($nama) <= 50 && preg_match("/^[A-Z\s'\.,\-]+$/u", $nama)) {
            return ['value' => $nama, 'confidence' => 1.0];
        }

        return ['value' => $nama, 'confidence' => 0.8];
    }

    private function extractTempatLahir(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:Tempat\s*(?:Lahir)?|TEMPAT\s*(?:LAHIR)?)\s*[:\.]?\s*(.+)/iu', $line, $matches)) {
                $candidate = $this->cleanValue($matches[1]);

                if (preg_match('/^\d/', $candidate) || $this->looksLikeLabel($candidate)) {
                    continue;
                }

                if ($candidate !== '') {
                    return ['value' => mb_strtoupper($candidate), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    private function extractTanggalLahir(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::DATE_PATTERN, $line, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];

                if ((int)$day >= 1 && (int)$day <= 31 && (int)$month >= 1 && (int)$month <= 12) {
                    return [
                        'value' => "{$day}-{$month}-{$year}",
                        'confidence' => 1.0,
                    ];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    private function extractAlamat(array $lines): array
    {
        $alamatIdx = -1;
        $alamatMain = '';

        foreach ($lines as $idx => $line) {
            if (preg_match('/(?:Alamat|ALAMAT)\s*[:\.]?\s*(.+)/iu', $line, $matches)) {
                $alamatIdx = $idx;
                $alamatMain = $this->cleanValue($matches[1]);
                break;
            }
        }

        if ($alamatIdx === -1) {
            return ['value' => '', 'confidence' => 0.0];
        }

        if ($alamatMain === '' && isset($lines[$alamatIdx + 1])) {
            $peek = $this->cleanValue($lines[$alamatIdx + 1]);

            if (
                $peek !== ''
                && !preg_match(self::RT_RW_PATTERN, $peek)
                && !preg_match('/(?:Kel\/?Desa|KEL\/?DESA)/iu', $peek)
                && !preg_match('/(?:Kecamatan|KECAMATAN)/iu', $peek)
            ) {
                $alamatMain = $peek;
            }
        }

        $parts = [];
        if ($alamatMain !== '') {
            $parts[] = $alamatMain;
        }

        $rtRw = $this->extractRtRw($lines, $alamatIdx);
        $kelDesa = $this->extractKelDesa($lines, $alamatIdx);
        $kecamatan = $this->extractKecamatan($lines, $alamatIdx);

        if ($rtRw['value'] !== '') {
            $parts[] = $rtRw['value'];
        }
        if ($kelDesa['value'] !== '') {
            $parts[] = $kelDesa['value'];
        }
        if ($kecamatan['value'] !== '') {
            $parts[] = $kecamatan['value'];
        }

        $address = implode(', ', $parts);

        $hasAllComponents = $alamatMain !== ''
            && $rtRw['value'] !== ''
            && $kelDesa['value'] !== ''
            && $kecamatan['value'] !== '';

        return [
            'value' => $address,
            'confidence' => $hasAllComponents ? 1.0 : 0.6,
        ];
    }

    private function extractRtRw(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match(self::RT_RW_PATTERN, $lines[$i], $matches)) {
                $rt = str_pad($matches[1], 3, '0', STR_PAD_LEFT);
                $rw = str_pad($matches[2], 3, '0', STR_PAD_LEFT);

                return [
                    'value' => "RT {$rt}/RW {$rw}",
                    'confidence' => 1.0,
                ];
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    private function extractKelDesa(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match('/(?:Kel\/?Desa|KEL\/?DESA|Kelurahan|KELURAHAN|Desa|DESA)\s*[:\.]?\s*(.+)/iu', $lines[$i], $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '') {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    private function extractKecamatan(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match('/(?:Kecamatan|KECAMATAN)\s*[:\.]?\s*(.+)/iu', $lines[$i], $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '') {
                    return ['value' => 'Kec. ' . mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    private function extractAgama(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:Agama|AGAMA)\s*[:\.]?\s*(ISLAM|KRISTEN|KATOLIK|HINDU|BUDDHA|BUDHA|KHONGHUCU)/iu', $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                return ['value' => $value, 'confidence' => 1.0];
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    private function extractStatusPerkawinan(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:Status\s*(?:Perkawinan)?|STATUS\s*(?:PERKAWINAN)?)\s*[:\.]?\s*(BELUM\s*KAWIN|KAWIN|CERAI\s*HIDUP|CERAI\s*MATI)/iu', $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));

                if (strpos($value, 'BELUM') !== false) {
                    return ['value' => 'BELUM KAWIN', 'confidence' => 1.0];
                }
                if (strpos($value, 'CERAI') !== false) {
                    if (strpos($value, 'HIDUP') !== false) {
                        return ['value' => 'CERAI HIDUP', 'confidence' => 1.0];
                    }
                    if (strpos($value, 'MATI') !== false) {
                        return ['value' => 'CERAI MATI', 'confidence' => 1.0];
                    }
                }
                return ['value' => 'KAWIN', 'confidence' => 1.0];
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    private function calculateOverallConfidence(array $fieldConfidences): float
    {
        $criticalFields = ['nik', 'nama_lengkap', 'alamat'];
        $criticalSum = 0;
        $criticalCount = 0;

        foreach ($criticalFields as $field) {
            if (isset($fieldConfidences[$field])) {
                $criticalSum += $fieldConfidences[$field];
                $criticalCount++;
            }
        }

        $criticalAvg = $criticalCount > 0 ? $criticalSum / $criticalCount : 0;

        $allSum = array_sum($fieldConfidences);
        $allCount = count($fieldConfidences);
        $allAvg = $allCount > 0 ? $allSum / $allCount : 0;

        return ($criticalAvg * 0.7) + ($allAvg * 0.3);
    }
}
