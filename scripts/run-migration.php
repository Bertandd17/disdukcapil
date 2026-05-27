#!/usr/bin/env php
<?php
/**
 * Railway Migration Runner
 * Executes raw SQL migration for Railway MySQL without needing full Laravel bootstrap.
 */

$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'railway';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

echo "Connecting to MySQL at {$host}:{$port}...\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Connected.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Migration SQL: Add 'Diterima' to antrian_online status_antrian enum
$sql = "ALTER TABLE `antrian_online` MODIFY COLUMN `status_antrian` ENUM(
    'Menunggu',
    'Dokumen Diterima',
    'Verifikasi Data',
    'Proses Cetak',
    'Siap Pengambilan',
    'Selesai',
    'Digunakan',
    'Ditolak',
    'Dibatalkan',
    'Diterima'
) NOT NULL DEFAULT 'Menunggu'";

try {
    $pdo->exec($sql);
    echo "Migration: Column 'Diterima' added to status_antrian enum.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'multiple primary key') !== false) {
        echo "Migration already applied (duplicate key or already exists).\n";
    } elseif (strpos($e->getMessage(), 'no侵害') !== false || strpos($e->getMessage(), 'no such') !== false) {
        echo "Table/column does not exist. Skipping.\n";
    } else {
        echo "Migration error: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
