<?php

namespace App\Console\Commands;

use App\Services\KtpOcrParsingService;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command untuk testing Google Vision API connection.
 * 
 * Usage:
 *   php artisan ocr:test
 *   php artisan ocr:test --image=path/to/image.jpg
 *   php artisan ocr:test --mock
 */
class TestKtpOcrCommand extends Command
{
    protected $signature = 'ocr:test 
                            {--image= : Path ke gambar KTP (default: test sample)}
                            {--mock : Gunakan mock response}';

    protected $description = 'Test Google Vision API connection untuk KTP OCR';

    private KtpOcrParsingService $parsingService;

    public function __construct(KtpOcrParsingService $parsingService)
    {
        parent::__construct();
        $this->parsingService = $parsingService;
    }

    public function handle(): int
    {
        $this->info('🧪 Testing Google Vision API Connection...');
        $this->newLine();

        // Check credentials
        $credentialsPath = $this->getCredentialsPath();
        
        if ($this->option('mock') || !file_exists($credentialsPath)) {
            $this->warn('⚠️  Running in MOCK mode');
            $this->newLine();
            return $this->runMockTest();
        }

        $this->info("📄 Credentials path: {$credentialsPath}");

        if (!file_exists($credentialsPath)) {
            $this->error("❌ Credentials file not found!");
            $this->newLine();
            $this->info('💡 Solution:');
            $this->line('   1. Download Service Account JSON from GCP Console');
            $this->line('   2. Save to: storage/app/google-creds.json');
            $this->line('   3. Or run with --mock to test parsing logic');
            $this->newLine();
            return self::FAILURE;
        }

        $this->info('✅ Credentials file found');
        $this->newLine();

        // Test Vision API connection
        try {
            $this->testVisionApiConnection($credentialsPath);
        } catch (\Throwable $e) {
            $this->error("❌ Vision API Error: " . $e->getMessage());
            $this->newLine();
            
            if (str_contains($e->getMessage(), '403')) {
                $this->info('💡 Possible solutions:');
                $this->line('   1. Enable Cloud Vision API in GCP Console');
                $this->line('   2. Add billing account (required by GCP)');
                $this->line('   3. Or use --mock for testing without credentials');
            }
            
            return self::FAILURE;
        }

        // Test with sample image if provided
        $imagePath = $this->option('image');
        if ($imagePath && file_exists($imagePath)) {
            $this->newLine();
            $this->testOcrWithImage($credentialsPath, $imagePath);
        }

        return self::SUCCESS;
    }

    private function testVisionApiConnection(string $credentialsPath): void
    {
        $this->info('🔄 Connecting to Google Vision API...');
        
        $clientOptions = ['credentials' => $credentialsPath];
        $client = new ImageAnnotatorClient($clientOptions);
        
        // Create a simple test image (1x1 pixel)
        $testImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        
        $image = new \Google\Cloud\Vision\V1\Image();
        $image->setContent($testImageContent);
        
        $response = $client->documentTextDetection($image);
        $client->close();

        $this->info('✅ Google Vision API connection successful!');
        $this->line('   - API is responsive');
        $this->line('   - Credentials are valid');
        $this->newLine();
    }

    private function testOcrWithImage(string $credentialsPath, string $imagePath): void
    {
        $this->info("📷 Testing OCR with image: {$imagePath}");
        
        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            $this->error("❌ Failed to read image file");
            return;
        }

        try {
            $clientOptions = ['credentials' => $credentialsPath];
            $client = new ImageAnnotatorClient($clientOptions);
            
            $image = new \Google\Cloud\Vision\V1\Image();
            $image->setContent($imageData);
            
            $response = $client->documentTextDetection($image);
            $client->close();

            $fullText = $response->getFullTextAnnotation();
            $rawText = $fullText !== null ? (string) $fullText->getText() : '';

            $this->info('✅ OCR completed!');
            $this->newLine();
            
            $this->info('📝 Raw OCR Text:');
            $this->line(str_repeat('-', 60));
            $this->line($rawText ?: '(No text detected)');
            $this->line(str_repeat('-', 60));
            $this->newLine();

            // Parse the result
            if ($rawText !== '') {
                $this->info('🔍 Parsing KTP Data...');
                $parsed = $this->parsingService->parse($rawText);
                $this->displayParsedData($parsed);
            } else {
                $this->warn('⚠️  No text detected in image');
            }

        } catch (\Throwable $e) {
            $this->error("❌ OCR Error: " . $e->getMessage());
            Log::error('OCR Test Error', [
                'error' => $e->getMessage(),
                'image' => $imagePath,
            ]);
        }
    }

    private function runMockTest(): int
    {
        $this->info('🔄 Running Mock OCR Test...');
        $this->newLine();

        // Sample OCR text (simulated)
        $mockText = <<<EOT
PROVINSI JAWA BARAT
KOTA BANDUNG
NIK : 3273011708900001
Nama : BUDI SANTOSO
Tempat/Tgl Lahir : BANDUNG, 17-08-1990
Jenis Kelamin : LAKI-LAKI / Gol. Darah : A
Alamat : JL. Asia Afrika No. 100
RT/RW : 001/002
Kel/Desa : SUMUR BANDUNG
Kecamatan : BANDUNG WETAN
Kabupaten/Kota : KOTA BANDUNG
Provinsi : JAWA BARAT
Agama : ISLAM
Status Perkawinan : KAWIN
Pekerjaan : PEGAWAI NEGERI SIPIL
Kewarganegaraan : WNI
EOT;

        $this->info('📝 Simulated OCR Text:');
        $this->line(str_repeat('-', 60));
        $this->line($mockText);
        $this->line(str_repeat('-', 60));
        $this->newLine();

        $this->info('🔍 Parsing KTP Data...');
        $parsed = $this->parsingService->parse($mockText);
        $this->displayParsedData($parsed);
        
        return self::SUCCESS;
    }

    private function displayParsedData(array $parsed): void
    {
        $this->newLine();
        $this->info('📋 Parsed KTP Data:');
        $this->line(str_repeat('-', 60));
        
        $fields = [
            'NIK' => $parsed['nik'] ?: '(not detected)',
            'Nama Lengkap' => $parsed['nama_lengkap'] ?: '(not detected)',
            'Tempat Lahir' => $parsed['tempat_lahir'] ?: '(not detected)',
            'Tanggal Lahir' => $parsed['tanggal_lahir'] ?: '(not detected)',
            'Jenis Kelamin' => $parsed['jenis_kelamin'] ?: '(not detected)',
            'Gol. Darah' => $parsed['gol_darah'] ?: '(not detected)',
            'Alamat' => $parsed['alamat'] ?: '(not detected)',
            'RT/RW' => $parsed['rt_rw'] ?: '(not detected)',
            'Kel/Desa' => $parsed['kel_desa'] ?: '(not detected)',
            'Kecamatan' => $parsed['kecamatan'] ?: '(not detected)',
            'Kab/Kota' => $parsed['kab_kota'] ?: '(not detected)',
            'Provinsi' => $parsed['provinsi'] ?: '(not detected)',
            'Agama' => $parsed['agama'] ?: '(not detected)',
            'Status Kawin' => $parsed['status_perkawinan'] ?: '(not detected)',
            'Pekerjaan' => $parsed['pekerjaan'] ?: '(not detected)',
            'Kewarganegaraan' => $parsed['kewarganegaraan'] ?: '(not detected)',
        ];

        foreach ($fields as $label => $value) {
            $this->line(sprintf('%-15s: %s', $label, $value));
        }

        $this->line(str_repeat('-', 60));
        $this->newLine();
        
        $this->info("📊 Overall Confidence: " . number_format($parsed['confidence'] * 100, 1) . '%');
        $this->newLine();

        // Field confidence
        $this->info('📈 Field Confidence:');
        foreach ($parsed['field_confidence'] as $field => $conf) {
            $bar = $this->generateConfidenceBar($conf);
            $this->line(sprintf('  %-20s %s %.0f%%', $field, $bar, $conf * 100));
        }
    }

    private function generateConfidenceBar(float $confidence): string
    {
        $filled = (int) round($confidence * 20);
        $empty = 20 - $filled;
        
        $color = $confidence >= 0.8 ? '🟢' : ($confidence >= 0.5 ? '🟡' : '🔴');
        
        return str_repeat('█', $filled) . str_repeat('░', $empty) . ' ' . $color;
    }

    private function getCredentialsPath(): string
    {
        $configPath = config('services.google_vision.credentials_path');
        if ($configPath) {
            return base_path($configPath);
        }

        $envPath = env('GOOGLE_VISION_CREDENTIALS');
        if ($envPath) {
            return base_path($envPath);
        }

        return base_path('storage/app/google-creds.json');
    }
}
