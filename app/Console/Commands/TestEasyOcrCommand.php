<?php

namespace App\Console\Commands;

use App\Services\EasyOcrService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Command untuk testing EasyOCR service
 * 
 * Usage:
 * php artisan ocr:test-easyocr
 * php artisan ocr:test-easyocr --image=path/to/image.jpg
 * php artisan ocr:test-easyocr --mock
 */
class TestEasyOcrCommand extends Command
{
    protected $signature = 'ocr:test-easyocr 
                            {--image= : Path ke gambar KTP untuk di-test}
                            {--mock : Gunakan mock response}';

    protected $description = 'Test EasyOCR service dengan gambar KTP';

    private EasyOcrService $ocrService;

    public function __construct(EasyOcrService $ocrService)
    {
        parent::__construct();
        $this->ocrService = $ocrService;
    }

    public function handle(): int
    {
        $this->info('🖼️  EasyOCR KTP Scanner Test');
        $this->info('=' . str_repeat('=', 40));

        // Cek apakah menggunakan mock
        $useMock = $this->option('mock') || config('services.easyocr.mock_enabled', true);
        
        if ($useMock) {
            $this->warn('⚠️  Mode MOCK aktif - hasil OCR adalah data simulasi');
            $this->info('');
        }

        // Ambil path gambar
        $imagePath = $this->option('image');

        if ($imagePath) {
            return $this->testWithImage($imagePath);
        }

        // Cari sample image
        return $this->testWithSampleImages();
    }

    private function testWithImage(string $imagePath): int
    {
        if (!file_exists($imagePath)) {
            $this->error("❌ File tidak ditemukan: {$imagePath}");
            return Command::FAILURE;
        }

        $this->info("📷 Testing dengan: {$imagePath}");
        $this->info('');

        // Buat uploaded file mock
        $file = new UploadedFile(
            $imagePath,
            basename($imagePath),
            mime_content_type($imagePath),
            null,
            true
        );

        $this->info('⏳ Memproses OCR...');
        $startTime = microtime(true);

        $result = $this->ocrService->processKtpImage($file);

        $processingTime = round(microtime(true) - $startTime, 2);

        return $this->displayResult($result, $processingTime);
    }

    private function testWithSampleImages(): int
    {
        $sampleDir = base_path('data/synthetic/ktp');
        
        if (!is_dir($sampleDir)) {
            $this->warn("⚠️  Direktori sample tidak ditemukan: {$sampleDir}");
            $this->info('Gunakan --image untuk menentukan path gambar');
            return Command::FAILURE;
        }

        // Cari 3 sample images
        $images = glob($sampleDir . '/*.jpg');
        
        if (empty($images)) {
            $images = glob($sampleDir . '/*.png');
        }

        if (empty($images)) {
            $this->error("❌ Tidak ada sample image ditemukan di {$sampleDir}");
            return Command::FAILURE;
        }

        // Ambil 3 sample pertama
        $samples = array_slice($images, 0, 3);

        $this->info("📂 Sample images: " . count($samples));
        $this->info('');

        $totalTime = 0;
        $successCount = 0;

        foreach ($samples as $index => $imagePath) {
            $this->info("--- Sample " . ($index + 1) . " ---");
            $this->info("📷 " . basename($imagePath));

            $file = new UploadedFile(
                $imagePath,
                basename($imagePath),
                mime_content_type($imagePath),
                null,
                true
            );

            $this->info('⏳ Processing...');
            
            $startTime = microtime(true);
            $result = $this->ocrService->processKtpImage($file);
            $time = round(microtime(true) - $startTime, 2);
            $totalTime += $time;

            if ($result['success']) {
                $successCount++;
                $this->info("✅ Success ({$time}s)");
                $this->displayDataSummary($result['data'] ?? []);
            } else {
                $this->error("❌ Gagal: " . ($result['message'] ?? 'Unknown error'));
            }
            
            $this->info('');
        }

        $this->info('=' . str_repeat('=', 40));
        $this->info("📊 Summary:");
        $this->info("   - Total samples: " . count($samples));
        $this->info("   - Success: {$successCount}");
        $this->info("   - Failed: " . (count($samples) - $successCount));
        $this->info("   - Average time: " . round($totalTime / count($samples), 2) . "s");

        return Command::SUCCESS;
    }

    private function displayResult(array $result, float $processingTime): int
    {
        $this->info('');
        $this->info("⏱️  Processing time: {$processingTime}s");
        $this->info('');

        if (!$result['success']) {
            $this->error("❌ GAGAL: " . ($result['message'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info('✅ SUKSES');
        $this->info('');

        // Confidence
        $confidence = $result['confidence'] ?? 0;
        $confidencePercent = round($confidence * 100);
        
        $this->info("📊 Confidence: {$confidencePercent}%");
        
        if ($confidence >= 0.8) {
            $this->info("   Status: 🟢 High");
        } elseif ($confidence >= 0.5) {
            $this->info("   Status: 🟡 Medium");
        } else {
            $this->info("   Status: 🔴 Low");
        }

        $this->info('');
        $this->info('📋 Extracted Data:');
        $this->info(str_repeat('-', 40));

        $this->displayDataSummary($result['data'] ?? []);

        // Raw text preview
        if (isset($result['raw_text']) && strlen($result['raw_text']) > 0) {
            $this->info('');
            $this->info('📄 Raw Text (preview):');
            $rawPreview = substr($result['raw_text'], 0, 500);
            if (strlen($result['raw_text']) > 500) {
                $rawPreview .= '...';
            }
            $this->line(wordwrap($rawPreview, 76));
        }

        return Command::SUCCESS;
    }

    private function displayDataSummary(array $data): void
    {
        $fields = [
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tgl Lahir',
            'jenis_kelamin' => 'JK',
            'gol_darah' => 'Gol Darah',
            'alamat' => 'Alamat',
            'rt_rw' => 'RT/RW',
            'kel_desa' => 'Kel/Desa',
            'kec' => 'Kecamatan',
            'kab_kota' => 'Kab/Kota',
            'provinsi' => 'Provinsi',
            'agama' => 'Agama',
            'status_perkawinan' => 'Status',
            'pekerjaan' => 'Pekerjaan',
            'kewarganegaraan' => 'Kewarganegaraan',
        ];

        foreach ($fields as $key => $label) {
            $value = $data[$key] ?? '-';
            
            if ($key === 'nik' && $value !== '-') {
                // Mask NIK untuk tampilan
                $value = substr($value, 0, 6) . '**********';
            }
            
            $this->info(sprintf(
                "   %-15s : %s",
                $label,
                $value
            ));
        }
    }
}
