<?php

namespace Database\Seeders;

use App\Models\AdminNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        AdminNotification::query()->delete();

        $notifications = [
            [
                'type' => AdminNotification::TYPE_LAYANAN_BARU,
                'title' => 'Permohonan KK Baru',
                'message' => 'Budi Santoso telah mengajukan permohonan Kartu Keluarga (No. Antrian: KK-121-001)',
                'link' => url('/admin/penerbitan-kk'),
                'is_read' => false,
                'created_at' => now()->subMinutes(5),
            ],
            [
                'type' => AdminNotification::TYPE_LAYANAN_BARU,
                'title' => 'Permohonan Akte Kelahiran',
                'message' => 'Siti Aminah telah mengajukan permohonan Akte Kelahiran (No. Antrian: AKL-121-002)',
                'link' => url('/admin/penerbitan-akte-lahir'),
                'is_read' => false,
                'created_at' => now()->subMinutes(15),
            ],
            [
                'type' => AdminNotification::TYPE_LAYANAN_BARU,
                'title' => 'Permohonan Akte Kematian',
                'message' => 'Ahmad Wijaya telah mengajukan permohonan Akte Kematian (No. Antrian: AKM-121-003)',
                'link' => url('/admin/penerbitan-akte-kematian'),
                'is_read' => false,
                'created_at' => now()->subMinutes(30),
            ],
            [
                'type' => AdminNotification::TYPE_LAYANAN_BARU,
                'title' => 'Permohonan Lahir Mati',
                'message' => 'Dewi Lestari telah mengajukan permohonan Lahir Mati (No. Antrian: LMT-121-004)',
                'link' => url('/admin/penerbitan-lahir-mati'),
                'is_read' => true,
                'created_at' => now()->subHours(1),
            ],
            [
                'type' => AdminNotification::TYPE_STATUS_UPDATE,
                'title' => 'Status Diperbarui',
                'message' => 'Antrian #ANT-120-099 telah diperbarui menjadi "Siap Pengambilan"',
                'link' => url('/admin/antrian-online'),
                'is_read' => true,
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($notifications as $notification) {
            AdminNotification::create($notification);
        }

        $this->command->info('✅ ' . count($notifications) . ' notifikasi dummy berhasil dibuat.');
    }
}
