<?php

namespace App\Console\Commands;

use App\Models\AntrianOnline;
use App\Models\Layanan_Model;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Artisan command untuk membuat data test antrian OCR.
 * 
 * Usage:
 *   php artisan ocr:create-test
 *   php artisan ocr:create-test --count=10
 */
class CreateTestAntrianCommand extends Command
{
    protected $signature = 'ocr:create-test 
                            {--count=5 : Jumlah data test yang akan dibuat}
                            {--clear : Hapus semua data test sebelumnya}';

    protected $description = 'Create test antrian data for OCR testing';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        
        if ($count > 100) {
            $this->warn('Maximum count is 100. Setting to 100.');
            $count = 100;
        }

        // Clear existing test data if requested
        if ($this->option('clear')) {
            $this->warn('Clearing existing test data...');
            AntrianOnline::where('nomor_antrian', 'LIKE', 'ANT%')
                ->whereDate('created_at', now()->toDateString())
                ->delete();
            $this->info('Existing test data cleared.');
            $this->newLine();
        }

        // Get or create default layanan
        $layananId = $this->getOrCreateDefaultLayanan();
        if (!$layananId) {
            $this->error('❌ Tidak dapat membuat test data karena tidak ada layanan.');
            $this->line('   Jalankan: php artisan db:seed --class=LayananSeeder');
            return self::FAILURE;
        }

        $this->info("Creating {$count} test antrian records...");
        $this->newLine();

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $created = 0;
        $createdIds = [];

        for ($i = 0; $i < $count; $i++) {
            $nomorAntrian = $this->generateNomorAntrian();
            
            try {
                $uuid = (string) Str::uuid();
                
                DB::table('antrian_online')->insert([
                    'antrian_online_id' => $uuid,
                    'nomor_antrian' => $nomorAntrian,
                    'nik' => '',
                    'nama_lengkap' => 'TEST_' . $uuid,
                    'alamat' => 'Alamat Test',
                    'status_antrian' => 'Menunggu',
                    'layanan_id' => $layananId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;
                $createdIds[] = $uuid;
                $bar->advance();
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Error creating antrian: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully created {$created} test antrian records.");
        $this->newLine();

        // Show the created records
        if (!empty($createdIds)) {
            $this->info('📋 Recent Test Records:');
            $records = AntrianOnline::whereIn('antrian_online_id', $createdIds)->get();

            $this->table(
                ['ID (UUID)', 'Nomor Antrian', 'Status'],
                $records->map(fn($r) => [
                    $r->antrian_online_id,
                    $r->nomor_antrian,
                    $r->status_antrian,
                ])
            );

            $this->newLine();
            $this->info('💡 Contoh penggunaan:');
            $this->line("   curl -X POST http://localhost:8000/api/ocr-direct/upload \\");
            $this->line("     -H 'Accept: application/json' \\");
            $this->line("     -F 'ktp_image=@path/to/ktp.jpg' \\");
            $this->line("     -F 'antrian_online_id={$createdIds[0]}'");
        }

        return self::SUCCESS;
    }

    private function getOrCreateDefaultLayanan(): ?string
    {
        // Coba ambil layanan pertama
        $layanan = Layanan_Model::first();
        if ($layanan) {
            return $layanan->layanan_id;
        }

        // Jika tidak ada, buat layanan default
        try {
            $uuid = (string) Str::uuid();
            
            DB::table('layanan')->insert([
                'layanan_id' => $uuid,
                'nama_layanan' => 'Layanan KTP Online',
                'deskripsi' => 'Layanan pengajuan KTP online',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->warn('⚠️  Membuat layanan default untuk testing.');
            
            return $uuid;
        } catch (\Throwable $e) {
            $this->error("Gagal membuat layanan default: " . $e->getMessage());
            return null;
        }
    }

    private function generateNomorAntrian(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'ANT';
        
        $lastAntrian = DB::table('antrian_online')
            ->whereDate('created_at', now()->toDateString())
            ->where('nomor_antrian', 'like', "{$prefix}{$date}%")
            ->orderBy('nomor_antrian', 'desc')
            ->first();

        if ($lastAntrian) {
            $lastNumber = (int) substr($lastAntrian->nomor_antrian, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }
}
