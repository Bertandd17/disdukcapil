<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * EasyOcrParsingService - Parsing hasil OCR KTP Indonesia
 * 
 * Didesain untuk memproses teks dari EasyOCR yang mungkin
 * memiliki karakter recognition error yang berbeda dari Google Vision.
 * 
 * Field yang di-parse:
 * - nik: 16 digit NIK
 * - nama_lengkap: Nama sesuai KTP
 * - tempat_lahir: Kota kelahiran
 * - tanggal_lahir: Tanggal lahir (DD-MM-YYYY)
 * - jenis_kelamin: LAKI-LAKI / PEREMPUAN
 * - gol_darah: A, B, AB, O (+/-)
 * - alamat: Alamat lengkap
 * - rt_rw: RT/RW
 * - kel_desa: Kelurahan/Desa
 * - kec: Kecamatan
 * - kab_kota: Kabupaten/Kota
 * - provinsi: Provinsi
 * - agama: Agama
 * - status_perkawinan: Status perkawinan
 * - pekerjaan: Pekerjaan
 * - kewarganegaraan: WNI/WNA
 * - berlaku_hingga: Tanggal berlaku KTP
 */
class EasyOcrParsingService
{
    /**
     * Kode provinsi Indonesia
     */
    private const VALID_PROVINCE_CODES = [
        '11', '12', '13', '14', '15', '16', '17', '18', '19', '21',
        '31', '32', '33', '34', '35', '36', '37',
        '51', '52', '53',
        '61', '62', '63', '64', '65',
        '71', '72', '73', '74', '75', '76', '94',
        '81', '82',
        '91', '92', '93', '94', '95', '96', '97', '98', '99',
    ];

    /**
     * Keyword noise yang bukan data
     */
    private const NOISE_KEYWORDS = [
        'PROVINSI', 'KOTA', 'KABUPATEN', 'KOTA', 'JAKARTA',
        'NIK', 'TEMPAt', 'TGL LAHIR', 'TANGGAL LAHIR',
        'JENIS KELAMIN', 'GOL DARAH', 'GOL. DARAH', 'ALAMAT',
        'RT/RW', 'KEL/DESA', 'KELURAHAN', 'DESA', 'KECAMATAN', 'AGAMA',
        'STATUS PERKAWINAN', 'PEKERJAAN', 'KEWARGANEGARAAN', 'BERLAKU HINGGA',
        'WNI', 'WNA', 'GOLONGAN', 'DARAH',
    ];

    /**
     * Parse raw OCR text menjadi data terstruktur
     *
     * @param  string  $rawText  Teks mentah dari EasyOCR
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
     *   berlaku_hingga: string,
     *   confidence: float,
     *   field_confidence: array<string, float>,
     *   raw_lines: array<int, string>
     * }
     */
    public function parse(string $rawText): array
    {
        // Normalisasi teks
        $cleanedText = $this->normalizeText($rawText);
        $lines = $this->splitLines($cleanedText);
        
        Log::debug('EasyOcrParsingService: Parsing started', [
            'line_count' => count($lines),
            'text_length' => strlen($rawText),
        ]);

        // Extract setiap field
        $nik = $this->extractNik($lines, $rawText);
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
        $berlaku = $this->extractBerlaku($lines);

        // Hitung confidence keseluruhan
        $fieldConfidences = [
            'nik' => $nik['confidence'],
            'nama_lengkap' => $nama['confidence'],
            'tempat_lahir' => $tempatLahir['confidence'],
            'tanggal_lahir' => $tanggalLahir['confidence'],
            'jenis_kelamin' => $jenisKelamin['confidence'],
            'gol_darah' => $golDarah['confidence'],
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
            'berlaku_hingga' => $berlaku['confidence'],
        ];

        $validFields = array_filter($fieldConfidences, fn($c) => $c > 0);
        $totalConfidence = count($validFields) > 0 
            ? array_sum($validFields) / count($fieldConfidences)
            : 0.0;

        Log::debug('EasyOcrParsingService: Parsing completed', [
            'confidence' => $totalConfidence,
            'valid_fields' => count($validFields),
            'total_fields' => count($fieldConfidences),
        ]);

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
            'kec' => $kecamatan['value'],
            'kab_kota' => $kabKota['value'],
            'provinsi' => $provinsi['value'],
            'agama' => $agama['value'],
            'status_perkawinan' => $statusKawin['value'],
            'pekerjaan' => $pekerjaan['value'],
            'kewarganegaraan' => $kewarganegaraan['value'],
            'berlaku_hingga' => $berlaku['value'],
            'confidence' => round($totalConfidence, 4),
            'field_confidence' => $fieldConfidences,
            'raw_lines' => $lines,
        ];
    }

    /**
     * Normalisasi teks OCR
     *
     * @param  string  $text
     * @return string
     */
    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        $lines = array_map(static function (string $line): string {
            return trim(preg_replace('/[ \t]+/', ' ', $line) ?? $line);
        }, $lines);

        $text = implode("\n", array_filter($lines, static fn (string $line): bool => $line !== ''));
        
        // Fix common EasyOCR mistakes
        $text = str_replace([
            '|',    // Sering salah baca sebagai pipe
            'I',    // Sering salah baca
            'l',    // Huruf L kecil
        ], [
            'I',
            'I',
            'l',
        ], $text);

        // Fix O/0 confusion
        // Huruf O biasanya lebih kecil dalam konteks NIK
        // tapi kita perlu konteks untuk memutuskan

        return trim($text);
    }

    /**
     * Split text menjadi lines
     *
     * @param  string  $text
     * @return array<int, string>
     */
    private function splitLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        
        return array_values(array_filter(
            array_map(fn($line) => trim($line), $lines),
            fn($line) => $line !== ''
        ));
    }

    /**
     * Normalisasi digit (O→0, I→1)
     *
     * @param  string  $value
     * @return string
     */
    private function normalizeDigits(string $value): string
    {
        $cleaned = preg_replace('/\s+/', '', $value);
        
        // Fix O→0 di context angka
        $cleaned = str_replace(['O', 'o'], '0', $cleaned);
        $cleaned = str_replace(['I', 'l'], '1', $cleaned);
        $cleaned = preg_replace('/[^0-9]/', '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Cek apakah baris adalah noise/label
     *
     * @param  string  $line
     * @return bool
     */
    private function isNoiseLine(string $line): bool
    {
        $upper = mb_strtoupper(trim($line));
        
        // Skip jika hanya label tanpa data
        if (preg_match('/^(NIK|TANGGAL|TEMPAT|JENIS|ALAMAT|RT|KEL|DESA|KEC|KABUPATEN|KOTA|PROVINSI|AGAMA|STATUS|PEKERJAAN|KEWARGANEGARAAN|BERLAKU)[\s:]*$/i', $upper)) {
            return true;
        }
        
        return false;
    }

    /**
     * Clean value dari noise
     *
     * @param  string  $value
     * @return string
     */
    private function cleanValue(string $value): string
    {
        $value = preg_replace('/\s+/', ' ', $value);
        $value = str_replace([':', '.', '-', ','], ' ', $value);
        $value = trim($value, " \t\n\r\0\x0B:.,-");
        
        return $value;
    }

    private function joinedText(array $lines): string
    {
        return trim(preg_replace('/\s+/', ' ', implode(' ', $lines)) ?? '');
    }

    private function extractInlineField(array $lines, string $startPattern, string $endPattern): string
    {
        $text = $this->joinedText($lines);
        if ($text === '') {
            return '';
        }

        $pattern = '/(?:' . $startPattern . ')\s*[:\.]?\s*(.*?)(?=\s*(?:' . $endPattern . ')\b|$)/iu';
        if (!preg_match($pattern, $text, $matches)) {
            return '';
        }

        return $this->cleanValue($matches[1] ?? '');
    }

    /**
     * Extract NIK dari lines
     *
     * @param  array<int, string>  $lines
     * @param  string  $fullText
     * @return array{value: string, confidence: float}
     */
    private function extractNik(array $lines, string $fullText): array
    {
        // Pattern untuk NIK: 16 digit angka
        $nikPattern = '/\b(\d{16})\b/';
        
        // Cari 16 digit berurutan di seluruh teks
        if (preg_match($nikPattern, $fullText, $matches)) {
            $candidate = $matches[1];
            
            // Validasi kode provinsi
            $province = substr($candidate, 0, 2);
            if (in_array($province, self::VALID_PROVINCE_CODES, true)) {
                return ['value' => $candidate, 'confidence' => 1.0];
            }
        }
        
        // Pattern dengan label
        $labelPattern = '/NIK\s*[:\.]?\s*([0-9OIl\s]{14,20})/iu';
        
        foreach ($lines as $line) {
            if (preg_match($labelPattern, $line, $matches)) {
                $normalized = $this->normalizeDigits($matches[1]);
                if (strlen($normalized) === 16) {
                    return ['value' => $normalized, 'confidence' => 0.9];
                }
            }
        }
        
        // Cari 16 digit yang mungkin terpisah
        $digitOnly = preg_replace('/\D/', '', $fullText);
        if (strlen($digitOnly) >= 16) {
            $candidate = substr($digitOnly, 0, 16);
            $province = substr($candidate, 0, 2);
            if (in_array($province, self::VALID_PROVINCE_CODES, true)) {
                return ['value' => $candidate, 'confidence' => 0.7];
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Nama dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractNama(array $lines): array
    {
        $nama = '';

        $inlineNama = $this->extractInlineField(
            $lines,
            'N\s*a\s*m\s*a|Nama',
            'Tempat\s*\/?\s*Tg[liI]?\s*Lahir|Tempat|TempauTg|Tg[liI]|Jenis|Jenla|Alamat|Aama|RT\s*\/\s*RW|Kel\s*\/?\s*Desa|Kecamatan|Agama|Aeam|Status|Pekerjaan|Kewarganegaraan|Berlaku|Brlau'
        );

        if ($inlineNama !== '' && !$this->isNoiseLine($inlineNama)) {
            $inlineNama = preg_replace('/\b(Tempat|Tg[liI]|Lahir|Jenis|Alamat)\b.*$/iu', '', $inlineNama) ?? $inlineNama;
            $inlineNama = trim(preg_replace("/[^A-Z\s'`]/iu", ' ', $inlineNama) ?? $inlineNama);
            $inlineNama = trim(preg_replace('/\s+/', ' ', $inlineNama) ?? $inlineNama);

            if (strlen($inlineNama) >= 3) {
                $nama = mb_strtoupper($inlineNama);
            }
        }
        
        // Cari baris dengan label Nama
        if ($nama === '') {
            foreach ($lines as $idx => $line) {
                if (preg_match('/(?:Nama|NAMA)\s*[:\.]?\s*(.+)/iu', $line, $matches)) {
                    $candidate = $this->cleanValue($matches[1]);

                    // Skip jika kosong atau noise
                    if ($candidate !== '' && !$this->isNoiseLine($candidate)) {
                        $nama = mb_strtoupper($candidate);
                        break;
                    }

                    // Jika kosong, coba baris berikutnya
                    if (isset($lines[$idx + 1]) && !$this->isNoiseLine($lines[$idx + 1])) {
                        $nama = mb_strtoupper($this->cleanValue($lines[$idx + 1]));
                        break;
                    }
                }
            }
        }
        
        // Fallback: cari baris dengan format nama (huruf besar, min 3 char)
        if ($nama === '') {
            foreach ($lines as $line) {
                $stripped = trim($line);
                
                // Skip noise dan baris pendek
                if (strlen($stripped) < 3 || $this->isNoiseLine($stripped)) {
                    continue;
                }
                
                // Skip jika mengandung digit
                if (preg_match('/\d/', $stripped)) {
                    continue;
                }
                
                // Skip jika terlihat seperti alamat
                if (preg_match('/^(JL|JALAN|RT|RW|KEL|KEC)/i', $stripped)) {
                    continue;
                }
                
                // Jika huruf besar semua atau pattern nama Indonesia
                if ($stripped === mb_strtoupper($stripped) || 
                    preg_match('/^[A-Z][a-z]+(\s+[A-Z][a-z]+)+$/', $stripped)) {
                    $nama = mb_strtoupper($stripped);
                    break;
                }
            }
        }
        
        if ($nama === '') {
            return ['value' => '', 'confidence' => 0.0];
        }
        
        // Validasi format nama
        if (preg_match("/^[A-Z\s'\.,\-]+$/u", $nama) && strlen($nama) >= 3 && strlen($nama) <= 100) {
            return ['value' => $nama, 'confidence' => 1.0];
        }
        
        return ['value' => $nama, 'confidence' => 0.8];
    }

    /**
     * Extract Tempat Lahir dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractTempatLahir(array $lines): array
    {
        $pattern = '/(?:Tempat(?:[\s\/]Tgl)?(?:[\s]Lahir)?|TEMPAT(?:[\s]LAHIR)?)\s*[:\.]?\s*([^,]+)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $candidate = $this->cleanValue($matches[1]);
                
                // Skip jika tanggal
                if (preg_match('/^\d{1,2}[\-\/]\d{1,2}[\-\/]\d{4}$/', trim($candidate))) {
                    continue;
                }
                
                if ($candidate !== '' && strlen($candidate) >= 2) {
                    return ['value' => mb_strtoupper($candidate), 'confidence' => 1.0];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Tanggal Lahir dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractTanggalLahir(array $lines): array
    {
        // Pattern untuk tanggal: DD-MM-YYYY atau DD/MM/YYYY
        $datePattern = '/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/';
        
        foreach ($lines as $line) {
            if (preg_match($datePattern, $line, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];
                
                // Validasi range
                if ((int)$day >= 1 && (int)$day <= 31 && 
                    (int)$month >= 1 && (int)$month <= 12 &&
                    (int)$year >= 1900 && (int)$year <= date('Y')) {
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
     * Extract Jenis Kelamin dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractJenisKelamin(array $lines): array
    {
        $pattern = '/(?:Jenis\s*(?:Kelamin)?|JENIS\s*(?:KELAMIN)?)\s*[:\.]?\s*(PEREMPUAN|LAKI[\- ]?LAKI|PEREMPUAN)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                
                // Normalisasi
                if (stripos($value, 'LAKI') !== false) {
                    return ['value' => 'LAKI-LAKI', 'confidence' => 1.0];
                }
                if (stripos($value, 'PEREMP') !== false) {
                    return ['value' => 'PEREMPUAN', 'confidence' => 1.0];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Golongan Darah dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractGolDarah(array $lines): array
    {
        $pattern = '/(?:Gol\.?\s*(?:Darah)?|GOLONGAN\s*(?:DARAH)?)\s*[:\.]?\s*([ABO][\+\-]?)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                return ['value' => $value, 'confidence' => 1.0];
            }
        }
        
        // Cari di baris yang sama dengan Jenis Kelamin
        foreach ($lines as $line) {
            if (preg_match('/(LAKI[\- ]?LAKI|PEREMPUAN).*?(Gol|[\+\-]|A|B|AB|O)/iu', $line, $matches)) {
                if (isset($matches[2])) {
                    $goldar = trim($matches[2]);
                    if (in_array($goldar, ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])) {
                        return ['value' => $goldar, 'confidence' => 0.8];
                    }
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Alamat dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractAlamat(array $lines): array
    {
        $alamatIdx = -1;
        $alamatParts = [];

        $inlineAlamat = $this->extractInlineField(
            $lines,
            'Alamat|ALAMAT|Aama',
            'RT\s*\/?\s*RW|ATaW|Kel\.?\s*\/?\s*Desa|KelDeaa|Kelurahan|Kecamatan|Keeapapn|Kabupaten|Kota|Provinsi|Agama|Aeam|Status|BekaPekaranan|Pekerjaan|Kewarganegaraan|Kessaneanan|Berlaku|Brlau'
        );

        if ($inlineAlamat !== '' && !$this->isNoiseLine($inlineAlamat)) {
            return [
                'value' => mb_strtoupper($inlineAlamat),
                'confidence' => 1.0,
            ];
        }
        
        // Cari baris dengan label Alamat
        foreach ($lines as $idx => $line) {
            if (preg_match('/(?:Alamat|ALAMAT|Aama)\s*[:\.]?\s*(.+)/iu', $line, $matches)) {
                $alamatIdx = $idx;
                $alamatParts[] = $this->cleanValue($matches[1]);
                break;
            }
        }
        
        if ($alamatIdx === -1) {
            return ['value' => '', 'confidence' => 0.0];
        }
        
        // Ambil baris-baris berikutnya sampai bertemu keyword stop
        $stopKeywords = ['RT/RW', 'RT', 'KEL', 'KEC', 'KABUPATEN', 'KOTA', 'PROVINSI', 'AGAMA', 'STATUS', 'PEKERJAAN', 'KEWARGANEGARAAN'];
        
        for ($i = $alamatIdx + 1; $i < count($lines) && $i < $alamatIdx + 5; $i++) {
            $line = trim($lines[$i]);
            $lineUpper = mb_strtoupper($line);
            
            // Stop jika ketemu keyword
            $shouldStop = false;
            foreach ($stopKeywords as $keyword) {
                if (strpos($lineUpper, $keyword) === 0 || preg_match('/^' . $keyword . '\s*[:\.]?\s*$/i', $lineUpper)) {
                    $shouldStop = true;
                    break;
                }
            }
            
            if ($shouldStop) {
                break;
            }
            
            // Skip jika hanya RT/RW
            if (preg_match('/^\d{3}\s*\/\s*\d{3}$/', $line)) {
                continue;
            }
            
            // Skip jika kosong atau noise
            if ($line !== '' && !$this->isNoiseLine($line)) {
                $alamatParts[] = $this->cleanValue($line);
            }
        }
        
        $alamat = implode(', ', array_filter($alamatParts));
        
        return [
            'value' => mb_strtoupper($alamat),
            'confidence' => count($alamatParts) > 0 ? 1.0 : 0.0,
        ];
    }

    /**
     * Extract RT/RW dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractRtRw(array $lines): array
    {
        $pattern = '/(?:RT\.?\s*\/?\s*RW\.?|RT\s*\/\s*RW)\s*[:\.]?\s*(\d{1,3})\s*\/?\s*(\d{1,3})/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $rt = str_pad($matches[1], 3, '0', STR_PAD_LEFT);
                $rw = str_pad($matches[2], 3, '0', STR_PAD_LEFT);
                return [
                    'value' => "RT {$rt}/RW {$rw}",
                    'confidence' => 1.0,
                ];
            }
        }
        
        // Pattern alternatif: RT 001/RW 002
        $altPattern = '/RT\s+(\d{1,3})\s*\/\s*RW\s+(\d{1,3})/iu';
        foreach ($lines as $line) {
            if (preg_match($altPattern, $line, $matches)) {
                $rt = str_pad($matches[1], 3, '0', STR_PAD_LEFT);
                $rw = str_pad($matches[2], 3, '0', STR_PAD_LEFT);
                return [
                    'value' => "RT {$rt}/RW {$rw}",
                    'confidence' => 0.9,
                ];
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Kelurahan/Desa dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractKelDesa(array $lines): array
    {
        $pattern = '/(?:Kel\.?\/?(?:Desa)?|KEL\/?DESA|Kelurahan|KELURAHAN|Desa|DESA)\s*[:\.]?\s*(.+)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '' && strlen($value) >= 2) {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Kecamatan dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractKecamatan(array $lines): array
    {
        $pattern = '/(?:Kecamatan|KECAMATAN)\s*[:\.]?\s*(.+)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '' && strlen($value) >= 2) {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Kabupaten/Kota dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractKabKota(array $lines): array
    {
        $pattern = '/(?:Kabupaten|KABUPATEN|Kota|KOTA)\s*[:\.]?\s*(.+)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '' && strlen($value) >= 2) {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Provinsi dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractProvinsi(array $lines): array
    {
        $pattern = '/(?:Provinsi|PROVINSI)\s*[:\.]?\s*(.+)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '' && strlen($value) >= 2) {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Agama dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractAgama(array $lines): array
    {
        $pattern = '/(?:Agama|AGAMA)\s*[:\.]?\s*(ISLAM|KRISTEN|KATOLIK|HINDU|BUDDHA|BUDHA|KHONGHUCU)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                
                // Normalisasi Budha → Buddha
                if ($value === 'BUDHA') {
                    $value = 'BUDDHA';
                }
                
                return ['value' => $value, 'confidence' => 1.0];
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Status Perkawinan dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractStatusKawin(array $lines): array
    {
        $pattern = '/(?:Status\s*(?:Perkawinan)?|STATUS\s*(?:PERKAWINAN)?)\s*[:\.]?\s*(BELUM\s*KAWIN|KAWIN|CERAI\s*HIDUP|CERAI\s*MATI)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = mb_strtoupper($this->cleanValue($matches[1]));
                return ['value' => $value, 'confidence' => 1.0];
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Pekerjaan dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractPekerjaan(array $lines): array
    {
        $pattern = '/(?:Pekerjaan|PEKERJAAN)\s*[:\.]?\s*(.+)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = $this->cleanValue($matches[1]);
                if ($value !== '' && !$this->isNoiseLine($value)) {
                    return ['value' => mb_strtoupper($value), 'confidence' => 1.0];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }

    /**
     * Extract Kewarganegaraan dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractKewarganegaraan(array $lines): array
    {
        $pattern = '/(?:Kewarganegaraan|KEWARGANEGARAAN)\s*[:\.]?\s*(WNI|WNA)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                return ['value' => mb_strtoupper($matches[1]), 'confidence' => 1.0];
            }
        }
        
        // Default ke WNI jika tidak ditemukan
        return ['value' => 'WNI', 'confidence' => 0.5];
    }

    /**
     * Extract Berlaku Hingga dari lines
     *
     * @param  array<int, string>  $lines
     * @return array{value: string, confidence: float}
     */
    private function extractBerlaku(array $lines): array
    {
        $pattern = '/(?:Berlaku\s*(?:Hingga)?|BERLAKU\s*(?:HINGGA)?)\s*[:\.]?\s*(.+)/iu';
        
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $value = $this->cleanValue($matches[1]);
                
                // Check jika SEUMUR HIDUP
                if (stripos($value, 'SEUMUR') !== false || stripos($value, 'SEPANJANG') !== false) {
                    return ['value' => 'SEUMUR HIDUP', 'confidence' => 1.0];
                }
                
                // Parse tanggal
                if (preg_match('/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/', $value, $dateMatches)) {
                    $day = str_pad($dateMatches[1], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($dateMatches[2], 2, '0', STR_PAD_LEFT);
                    $year = $dateMatches[3];
                    return [
                        'value' => "{$day}-{$month}-{$year}",
                        'confidence' => 1.0,
                    ];
                }
                
                if ($value !== '') {
                    return ['value' => mb_strtoupper($value), 'confidence' => 0.8];
                }
            }
        }
        
        return ['value' => '', 'confidence' => 0.0];
    }
}
