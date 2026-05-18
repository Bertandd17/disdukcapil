<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ktp:check-dataset {dir? : Path folder gambar (default: GCP_MOCK_DATASET_DIR atau model/dataset/Test)}', function (?string $dir = null): void {
    $base = $dir ?: config('services.gcp_ktp.mock_dataset_dir');
    if (! is_string($base) || $base === '') {
        $base = base_path('model'.DIRECTORY_SEPARATOR.'dataset'.DIRECTORY_SEPARATOR.'Test');
    }
    $resolved = realpath($base);
    if ($resolved === false || ! is_dir($resolved)) {
        $this->error('Folder tidak ada: '.$base);

        return;
    }

    $this->info('Memeriksa: '.$resolved);
    $images = array_values(array_unique(array_merge(
        glob($resolved.DIRECTORY_SEPARATOR.'*.jpg') ?: [],
        glob($resolved.DIRECTORY_SEPARATOR.'*.jpeg') ?: [],
        glob($resolved.DIRECTORY_SEPARATOR.'*.png') ?: [],
        glob($resolved.DIRECTORY_SEPARATOR.'*.JPG') ?: [],
        glob($resolved.DIRECTORY_SEPARATOR.'*.JPEG') ?: [],
        glob($resolved.DIRECTORY_SEPARATOR.'*.PNG') ?: [],
    )));
    $missing = [];
    foreach ($images as $imgPath) {
        $stem = pathinfo($imgPath, PATHINFO_FILENAME);
        $json = $resolved.DIRECTORY_SEPARATOR.$stem.'.json';
        if (! is_file($json)) {
            $missing[] = basename($imgPath).' → butuh '.basename($json);
        }
    }

    if ($images === []) {
        $this->warn('Tidak ada gambar .jpg/.jpeg/.png di folder ini.');
    } else {
        $this->info('Gambar ditemukan: '.count($images));
    }

    if ($missing === []) {
        $this->info('Semua gambar punya pasangan .json (atau tidak ada gambar).');

        return;
    }

    $this->warn('Tanpa fixture JSON, mode mock akan mengisi data RANDOM (bukan isi KTP):');
    foreach ($missing as $line) {
        $this->line(' - '.$line);
    }
    $this->newLine();
    $this->line('Lihat: model/dataset/Test/README.md');
})->purpose('Cek pasangan gambar KTP ↔ JSON untuk OCR mock');
