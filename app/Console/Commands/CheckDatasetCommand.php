<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Command untuk menampilkan gambar di dataset folder yang belum punya fixture JSON.
 * 
 * Usage:
 *   php artisan ktp:check-dataset
 */
class CheckDatasetCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ktp:check-dataset 
                            {--dir= : Custom dataset directory}
                            {--missing : Hanya tampilkan yang belum punya JSON}';

    /**
     * The console command description.
     */
    protected $description = 'Check dataset images and their JSON fixtures';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseDir = base_path('model/dataset');
        
        if (!$this->option('dir')) {
            // Check both Train and Test directories
            $testDir = $baseDir . '/Test';
            $trainDir = $baseDir . '/Train';
            
            $this->info('=== Dataset Check ===');
            $this->info('');
            
            $hasTestData = is_dir($testDir);
            $hasTrainData = is_dir($trainDir);
            
            if ($hasTestData) {
                $this->checkDirectory($testDir, 'Test');
            } else {
                $this->warn('Directory Test tidak ditemukan.');
            }
            
            if ($hasTrainData) {
                $this->checkDirectory($trainDir, 'Train');
            } else {
                $this->warn('Directory Train tidak ditemukan.');
            }
            
            return Command::SUCCESS;
        }
        
        $customDir = $this->option('dir');
        if (!is_dir($customDir)) {
            $this->error("Directory tidak ditemukan: {$customDir}");
            return Command::FAILURE;
        }
        
        $this->checkDirectory($customDir, basename($customDir));
        
        return Command::SUCCESS;
    }
    
    /**
     * Check directory for images and JSON fixtures.
     */
    private function checkDirectory(string $dir, string $label): void
    {
        $this->info("📁 Folder: {$label}");
        $this->line("   Path: {$dir}");
        $this->line('');
        
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $jsonFiles = [];
        $imageFiles = [];
        
        // Scan directory
        $files = File::files($dir);
        
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            $basename = $file->getBasename();
            
            if ($extension === 'json') {
                $jsonFiles[] = $basename;
            } elseif (in_array($extension, $imageExtensions)) {
                $imageFiles[] = $basename;
            }
        }
        
        // Statistics
        $this->line("   📊 Statistik:");
        $this->line("      - Total gambar: " . count($imageFiles));
        $this->line("      - Total fixture JSON: " . count($jsonFiles));
        
        // Images without JSON
        $imagesWithoutJson = [];
        foreach ($imageFiles as $image) {
            $stem = pathinfo($image, PATHINFO_FILENAME);
            $jsonName = $stem . '.json';
            
            if (!in_array($jsonName, $jsonFiles)) {
                $imagesWithoutJson[] = $image;
            }
        }
        
        if (count($imagesWithoutJson) > 0) {
            $this->line('');
            $this->warn("   ⚠️  Gambar tanpa fixture JSON (" . count($imagesWithoutJson) . "):");
            
            if ($this->option('missing')) {
                foreach ($imagesWithoutJson as $img) {
                    $this->line("      - {$img}");
                }
            } else {
                // Show first 10
                $shown = array_slice($imagesWithoutJson, 0, 10);
                foreach ($shown as $img) {
                    $this->line("      - {$img}");
                }
                
                if (count($imagesWithoutJson) > 10) {
                    $remaining = count($imagesWithoutJson) - 10;
                    $this->line("      ... dan {$remaining} file lainnya");
                }
            }
            
            $this->line('');
            $this->info("   💡 Tip: Buat fixture JSON dengan nama yang sama untuk hasil OCR yang sesuai.");
            $this->line("      Contoh: {$imagesWithoutJson[0]} → Buat file " . pathinfo($imagesWithoutJson[0], PATHINFO_FILENAME) . ".json");
        } else {
            $this->line('');
            $this->info("   ✅ Semua gambar sudah memiliki fixture JSON!");
        }
        
        // Show sample JSON structure
        if (count($jsonFiles) > 0 && !$this->option('missing')) {
            $this->line('');
            $this->line("   📝 Contoh fixture JSON:");
            $sampleJson = $this->getSampleJsonStructure();
            $this->line("      " . str_replace("\n", "\n      ", $sampleJson));
        }
        
        $this->line(str_repeat('-', 60));
        $this->line('');
    }
    
    /**
     * Get sample JSON structure.
     */
    private function getSampleJsonStructure(): string
    {
        $sample = [
            'nik' => '3273011708900001',
            'nama_lengkap' => 'NAMA SESUAI KTP',
            'alamat' => 'JL. CONTOH NO 1, RT 001 RW 002',
            'tempat_lahir' => 'KOTA',
            'tanggal_lahir' => '17-08-1990',
            'jenis_kelamin' => 'LAKI-LAKI',
            'gol_darah' => 'A',
            'agama' => 'ISLAM',
            'status_perkawinan' => 'KAWIN',
            'pekerjaan' => 'PEGAWAI NEGERI SIPIL',
            'kewarganegaraan' => 'WNI',
        ];
        
        return json_encode($sample, JSON_PRETTY_PRINT);
    }
}
