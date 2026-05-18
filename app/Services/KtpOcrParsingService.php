<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * KtpOcrParsingService - Parsing teks hasil OCR KTP Indonesia.
 * 
 * Logika parsing diadopsi dari serverless-ktp-ocr (rhzs/serverless-ktp-ocr)
 * dan diadaptasi untuk PHP/Laravel.
 * 
 * Ekstraksi field:
 * - NIK (16 digit)
 * - Nama lengkap
 * - Tempat & tanggal lahir
 * - Jenis kelamin
 * - Alamat (RT/RW, Kelurahan, Kecamatan, Kabupaten/Kota, Provinsi)
 * - Agama
 * - Status perkawinan
 * - Pekerjaan
 * - Kewarganegaraan
 */
class KtpOcrParsingService
{
    /**
     * Kode provinsi Indonesia yang valid
     */
    private const VALID_PROVINCE_CODES = [
        '11', '12', '13', '14', '15', '16', '17', '18', '19', '21',
        '31', '32', '33', '34', '35', '36',
        '51', '52', '53',
        '61', '62', '63', '64', '65',
        '71', '72', '73', '74', '75', '76',
        '81', '82',
        '91', '92', '94',
    ];

    /**
     * Keyword yang menunjukkan baris adalah label/bukan data
     */
    private const NOISE_KEYWORDS = [
        'PROVINSI', 'KOTA', 'KABUPATEN', 'JAKARTA', 'NIK', 'TEMPAT', 'TGL LAHIR',
        'TANGGAL LAHIR', 'JENIS KELAMIN', 'GOL DARAH', 'GOL. DARAH', 'ALAMAT',
        'RT/RW', 'KEL/DESA', 'KELURAHAN', 'DESA', 'KECAMATAN', 'AGAMA',
        'STATUS PERKAWINAN', 'PEKERJAAN', 'KEWARGANEGARAAN', 'BERLAKU HINGGA',
        'WNI', 'WNA', 'ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'BUDHA',
        'KHONGHUCU', 'BELUM KAWIN', 'KAWIN', 'CERAI',
    ];

    /**
     * Pattern untuk ekstraksi NIK
     */
    private const NIK_LABEL_RE = '/(?:NIK|N\s*I\s*K)\s*[:\.]?\s*([0-9OoIl\s]{14,22})/iu';
    private const NIK_RAW_RE = '/\b\d{16}\b/';

    /**
     * Pattern untuk ekstraksi Nama
     */
    private const NAMA_LABEL_RE = '/(?:Nama|NAMA|N\s*a\s*m\s*a)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi Alamat
     */
    private const ALAMAT_LABEL_RE = '/(?:Alamat|ALAMAT)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi RT/RW
     */
    private const RT_RW_RE = '/(?:RT.?\/RW.?|RT\.\s*\/\s*RW\.?)\s*[:\.]?\s*(\d{1,3})\s*\/\s*(\d{1,3})/iu';

    /**
     * Pattern untuk ekstraksi Kelurahan/Desa
     */
    private const KEL_RE = '/(?:Kel\/?Desa|KEL\/?DESA|Kelurahan|KELURAHAN|Desa|DESA)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi Kecamatan
     */
    private const KEC_RE = '/(?:Kecamatan|KECAMATAN)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi Kabupaten/Kota
     */
    private const KAB_KOTA_RE = '/(?:Kabupaten|KABUPATEN|Kota|KOTA)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi Provinsi
     */
    private const PROV_RE = '/(?:Provinsi|PROVINSI)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi Tempat Lahir
     */
    private const TEMPAT_LAHIR_RE = '/(?:Tempat\s*(?:Lahir)?|TEMPAT\s*(?:LAHIR)?)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi Tanggal Lahir
     */
    private const TANGGAL_LAHIR_RE = '/(?:Tgl\s*(?:Lahir)?|Tanggal\s*(?:Lahir)?|TANGGAL\s*(?:LAHIR)?)\s*[:\.]?\s*([\d\/\-]+)/iu';

    /**
     * Pattern untuk ekstraksi Jenis Kelamin
     */
    private const JENIS_KELAMIN_RE = '/(?:Jenis\s*(?:Kelamin)?|JENIS\s*(?:KELAMIN)?)\s*[:\.]?\s*(PEREMPUAN|LAKI-LAKI|LAKI ?LAKI|PEREMPUAN)/iu';

    /**
     * Pattern untuk ekstraksi Golongan Darah
     */
    private const GOLDARAH_RE = '/(?:Gol\.?\s*(?:Darah)?|GOLONGAN\s*(?:DARAH)?)\s*[:\.]?\s*([ABO][\+\-]?)/iu';

    /**
     * Pattern untuk ekstraksi Agama
     */
    private const AGAMA_RE = '/(?:Agama|AGAMA)\s*[:\.]?\s*(ISLAM|KRISTEN|KATOLIK|HINDU|BUDDHA|BUDHA|KHONGHUCU)/iu';

    /**
     * Pattern untuk ekstraksi Status Perkawinan
     */
    private const STATUS_KAWIN_RE = '/(?:Status\s*(?:Perkawinan)?|STATUS\s*(?:PERKAWINAN)?)\s*[:\.]?\s*(BELUM\s*KAWIN|KAWIN|CERAI\s*HIDUP|CERAI\s*MATI)/iu';

    /**
     * Pattern untuk ekstraksi Pekerjaan
     */
    private const PEKERJAAN_RE = '/(?:Pekerjaan|PEKERJAAN)\s*[:\.]?\s*(.+)/iu';

    /**
     * Pattern untuk ekstraksi Kewarganegaraan
     */
    private const KEWARGANEGARAAN_RE = '/(?:Kewarganegaraan|KEWARGANEGARAAN)\s*[:\.]?\s*(WNI|WNA)/iu';

    /**
     * Parse raw OCR text menjadi data terstruktur.
     *
     * @param  string  $rawText  Raw text dari Google Vision API
     * @return array{
     *   nik: string,
     *   nama_lengkap: string,
     *   tempat_lahir: string,
     *   tanggal_lahir: string,
     *   jenis_kelamin: string,
     *   gol_darah: string,
     *   alamat: string,
     *   rt_rw: string,
     *   kel_desa: string,
     *   kec: string,
     *   kab_kota: string,
     *   provinsi: string,
     *   agama: string,
     *   status_perkawinan: string,
     *   pekerjaan: string,
     *   kewarganegaraan: string,
     *   confidence: float,
     *   field_confidence: array<string, float>,
     *   raw_lines: array<int, string>
     * }
     */
    public function parse(string $rawText): array
    {
        // Normalisasi teks: hapus carriage return, split per baris
        $lines = $this->splitLines($rawText);
        
        Log::debug('KtpOcrParsingService: processing', [
            'line_count' => count($lines),
            'text_length' => strlen($rawText),
        ]);

        // Ekstrak setiap field
        $nik = $this->extractNik($lines);
        $nama = $this->extractNama($lines);
        $tempatLahir = $this->extractTempatLahir($lines);
        $tanggalLahir = $this->extractTanggalLahir($lines);
        $jenisKelamin = $this->extractJenisKelamin($lines);
        $golDarah = $this->extractGolDarah($lines);
        $alamat = $this->extractAlamat($lines);
        $rtRw = $this->extractRtRw($lines);
        $kelDesa = $this->extractKelDesa($lines);
        $kecamatan = $this->extractKecamatan($lines);
        $kabKota = $this->extractKabKota($lines);
        $provinsi = $this->extractProvinsi($lines);
        $agama = $this->extractAgama($lines);
        $statusKawin = $this->extractStatusKawin($lines);
        $pekerjaan = $this->extractPekerjaan($lines);
        $kewarganegaraan = $this->extractKewarganegaraan($lines);

        // Hitung confidence keseluruhan
        $fieldConfidences = [
            'nik' => $nik['confidence'],
            'nama_lengkap' => $nama['confidence'],
            'tempat_lahir' => $tempatLahir['confidence'],
            'tanggal_lahir' => $tanggalLahir['confidence'],
            'jenis_kelamin' => $jenisKelamin['confidence'],
            'alamat' => $alamat['confidence'],
            'rt_rw' => $rtRw['confidence'],
            'kel_desa' => $kelDesa['confidence'],
            'kecamatan' => $kecamatan['confidence'],
            'kab_kota' => $kabKota['confidence'],
            'provinsi' => $provinsi['confidence'],
            'agama' => $agama['confidence'],
            'status_perkawinan' => $statusKawin['confidence'],
            'pekerjaan' => $pekerjaan['confidence'],
            'kewarganegaraan' => $kewarganegaraan['confidence'],
        ];

        $totalConfidence = array_sum($fieldConfidences) / count($fieldConfidences);

        return [
            'nik' => $nik['value'],
            'nama_lengkap' => $nama['value'],
            'tempat_lahir' => $tempatLahir['value'],
            'tanggal_lahir' => $tanggalLahir['value'],
            'jenis_kelamin' => $jenisKelamin['value'],
            'gol_darah' => $golDarah['value'],
            'alamat' => $alamat['value'],
            'rt_rw' => $rtRw['value'],
            'kel_desa' => $kelDesa['value'],
            'kecamatan' => $kecamatan['value'],
            'kab_kota' => $kabKota['value'],
            'provinsi' => $provinsi['value'],
            'agama' => $agama['value'],
            'status_perkawinan' => $statusKawin['value'],
            'pekerjaan' => $pekerjaan['value'],
            'kewarganegaraan' => $kewarganegaraan['value'],
            'confidence' => round($totalConfidence, 4),
            'field_confidence' => $fieldConfidences,
            'raw_lines' => $lines,
        ];
    }

    /**
     * Split raw text menjadi array baris.
     *
     * @param  string  $text
     * @return array<int, string>
     */
    private function splitLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        
        return array_values(array_filter(
            array_map(fn ($line) => trim($line), $lines),
            fn ($line) => $line !== ''
        ));
    }

    /**
     * Normalisasi digit dari NIK候选人 (O→0, I/l→1, hapus spasi).
     *
     * @param  string  $value
     * @return string
     */
    private function normalizeDigits(string $value): string
    {
        $cleaned = preg_replace('/\s+/', '', $value);
        $cleaned = str_replace(['O', 'o'], '0', $cleaned);
        $cleaned = str_replace(['I', 'l'], '1', $cleaned);
        $cleaned = preg_replace('/\D/', '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Cek apakah baris terlihat seperti label/header.
     *
     * @param  string  $line
     * @return bool
     */
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

    /**
     * Bersihkan value dari noise.
     *
     * @param  string  $value
     * @return string
     */
    private function cleanValue(string $value): string
    {
        $value = preg_replace('/\s+/', ' ', $value);
        $value = str_replace([':', '.', '-', ','], ' ', $value);
        
        return trim($value, " \t\n\r\0\x0B:.-,");
    }

    /**
     * Ekstrak NIK dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractNik(array $lines): array
    {
        $candidate = '';

        // Coba cari dengan pattern label
        foreach ($lines as $line) {
            if (preg_match(self::NIK_LABEL_RE, $line, $matches)) {
                $candidate = $this->normalizeDigits($matches[1]);
                if (strlen($candidate) === 16) {
                    break;
                }
            }
        }

        // Jika tidak ketemu, cari 16 digit berurutan di seluruh teks
        if (strlen($candidate) !== 16) {
            $joined = implode(' ', $lines);
            if (preg_match(self::NIK_RAW_RE, $joined, $matches)) {
                $candidate = $matches[0];
            }
        }

        if (strlen($candidate) !== 16) {
            return ['value' => '', 'confidence' => 0.0];
        }

        // Validasi kode provinsi
        $province = substr($candidate, 0, 2);
        $isValidProvince = in_array($province, self::VALID_PROVINCE_CODES, true);
        
        return [
            'value' => $candidate,
            'confidence' => $isValidProvince ? 1.0 : 0.7,
        ];
    }

    /**
     * Ekstrak Nama Lengkap dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractNama(array $lines): array
    {
        $nama = '';

        // Cari baris dengan label Nama
        foreach ($lines as $idx => $line) {
            if (preg_match(self::NAMA_LABEL_RE, $line, $matches)) {
                $candidate = $this->cleanValue($matches[1]);
                
                // Jika value kosong, coba ambil baris berikutnya
                if ($candidate === '' && isset($lines[$idx + 1])) {
                    $candidate = $this->cleanValue($lines[$idx + 1]);
                }
                
                if ($candidate !== '' && !$this->looksLikeLabel($candidate)) {
                    $nama = $candidate;
                    break;
                }
            }
        }

        // Fallback: cari baris yang terlihat seperti nama (huruf besar semua)
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

    /**
     * Ekstrak Tempat Lahir dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractTempatLahir(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::TEMPAT_LAHIR_RE, $line, $matches)) {
                $candidate = $this->cleanValue($matches[1]);
                
                // Skip jika candidate adalah tanggal atau label lain
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

    /**
     * Ekstrak Tanggal Lahir dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractTanggalLahir(array $lines): array
    {
        foreach ($lines as $line) {
            // Pattern untuk tanggal dalam format Indonesia: 17-08-1990 atau 17/08/1990
            if (preg_match('/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/', $line, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];
                
                // Validasi range
                if ((int)$day >= 1 && (int)$day <= 31 && (int)$month >= 1 && (int)$month <= 12) {
                    return [
                        'value' => "{$day}-{$month}-{$year}",
                        'confidence' => 1.0,
                    ];
                }
            }
            
            // Pattern untuk label Tanggal Lahir
            if (preg_match(self::TANGGAL_LAHIR_RE, $line, $matches)) {
                $candidate = $this->cleanValue($matches[1]);
                if (preg_match('/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/', $candidate, $dateMatches)) {
                    $day = str_pad($dateMatches[1], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($dateMatches[2], 2, '0', STR_PAD_LEFT);
                    $year = $dateMatches[3];
                    
                    return [
                        'value' => "{$day}-{$month}-{$year}",
                        'confidence' => 1.0,
                    ];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Jenis Kelamin dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractJenisKelamin(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::JENIS_KELAMIN_RE, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                
                // Normalisasi
                if (strpos($value, 'LAKI') !== false) {
                    return ['value' => 'LAKI-LAKI', 'confidence' => 1.0];
                }
                if (strpos($value, 'PEREMP') !== false) {
                    return ['value' => 'PEREMPUAN', 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Golongan Darah dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractGolDarah(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::GOLDARAH_RE, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                return ['value' => $value, 'confidence' => 1.0];
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Alamat lengkap dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractAlamat(array $lines): array
    {
        $alamatIdx = -1;
        $alamatMain = '';

        // Cari baris dengan label Alamat
        foreach ($lines as $idx => $line) {
            if (preg_match(self::ALAMAT_LABEL_RE, $line, $matches)) {
                $alamatIdx = $idx;
                $alamatMain = $this->cleanValue($matches[1]);
                break;
            }
        }

        if ($alamatIdx === -1) {
            return ['value' => '', 'confidence' => 0.0];
        }

        // Jika alamat utama kosong, coba baris berikutnya
        if ($alamatMain === '' && isset($lines[$alamatIdx + 1])) {
            $peek = $this->cleanValue($lines[$alamatIdx + 1]);
            
            // Skip jika itu adalah RT/RW, Kel, atau Kec
            if (
                $peek !== ''
                && !preg_match(self::RT_RW_RE, $peek)
                && !preg_match(self::KEL_RE, $peek)
                && !preg_match(self::KEC_RE, $peek)
            ) {
                $alamatMain = $peek;
            }
        }

        // Ekstrak komponen alamat dari baris-baris setelah Alamat
        $rtRw = $this->extractRtRw($lines, $alamatIdx);
        $kelDesa = $this->extractKelDesa($lines, $alamatIdx);
        $kecamatan = $this->extractKecamatan($lines, $alamatIdx);
        $kabKota = $this->extractKabKota($lines, $alamatIdx);
        $provinsi = $this->extractProvinsi($lines, $alamatIdx);

        // Gabungkan komponen alamat
        $parts = [];
        if ($alamatMain !== '') {
            $parts[] = $alamatMain;
        }

        $hasAllComponents = $alamatMain !== ''
            && $rtRw['value'] !== ''
            && $kelDesa['value'] !== ''
            && $kecamatan['value'] !== '';

        $address = implode(', ', $parts);
        
        return [
            'value' => $address,
            'confidence' => $hasAllComponents ? 1.0 : 0.6,
        ];
    }

    /**
     * Ekstrak RT/RW dari baris.
     *
     * @param  array<int, string>  $lines
     * @param  int|null  $startIdx  Mulai cari dari index ini
     * @return array{value: string, confidence: float}
     */
    private function extractRtRw(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match(self::RT_RW_RE, $lines[$i], $matches)) {
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

    /**
     * Ekstrak Kelurahan/Desa dari baris.
     *
     * @param  array<int, string>  $lines
     * @param  int|null  $startIdx  Mulai cari dari index ini
     * @return array{value: string, confidence: float}
     */
    private function extractKelDesa(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match(self::KEL_RE, $lines[$i], $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '') {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Kecamatan dari baris.
     *
     * @param  array<int, string>  $lines
     * @param  int|null  $startIdx  Mulai cari dari index ini
     * @return array{value: string, confidence: float}
     */
    private function extractKecamatan(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match(self::KEC_RE, $lines[$i], $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '') {
                    return ['value' => 'Kec. ' . mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Kabupaten/Kota dari baris.
     *
     * @param  array<int, string>  $lines
     * @param  int|null  $startIdx  Mulai cari dari index ini
     * @return array{value: string, confidence: float}
     */
    private function extractKabKota(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match(self::KAB_KOTA_RE, $lines[$i], $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '') {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Provinsi dari baris.
     *
     * @param  array<int, string>  $lines
     * @param  int|null  $startIdx  Mulai cari dari index ini
     * @return array{value: string, confidence: float}
     */
    private function extractProvinsi(array $lines, ?int $startIdx = null): array
    {
        $start = $startIdx !== null ? $startIdx + 1 : 0;
        $lookUntil = min(count($lines), $start + 7);

        for ($i = $start; $i < $lookUntil; $i++) {
            if (preg_match(self::PROV_RE, $lines[$i], $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '') {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Agama dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractAgama(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::AGAMA_RE, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                return ['value' => $value, 'confidence' => 1.0];
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Status Perkawinan dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractStatusKawin(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::STATUS_KAWIN_RE, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                // Normalisasi
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

    /**
     * Ekstrak Pekerjaan dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractPekerjaan(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::PEKERJAAN_RE, $line, $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '' && !$this->looksLikeLabel($value)) {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }

        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Ekstrak Kewarganegaraan dari baris.
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractKewarganegaraan(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match(self::KEWARGANEGARAAN_RE, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                return ['value' => $value, 'confidence' => 1.0];
            }
        }

        // Default ke WNI jika tidak ditemukan
        return ['value' => 'WNI', 'confidence' => 0.5];
    }
}
