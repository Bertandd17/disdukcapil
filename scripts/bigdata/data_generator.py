#!/usr/bin/env python3
"""
Data Generator untuk Big Data Project
Disdukcapil Kabupaten Toba - Big Data Integration

Membuat simulasi data kependudukan >100.000 record
Output: CSV dan Parquet files untuk Apache Spark processing

Usage:
    python data_generator.py --records 100000 --output data/bigdata
"""

import os
import sys
import argparse
import random
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from pathlib import Path

# Try importing Faker, use basic fallback if not available
try:
    from faker import Faker
    fake = Faker('id_ID')
    HAS_FAKER = True
except ImportError:
    HAS_FAKER = False
    print("Warning: Faker not installed. Using basic name generator.")

# Configuration
KECAMATAN_TOBA = [
    'Balige', 'Porsea', 'Laguboti', 'Borbor', 'Sigumpar',
    'Ajibata', 'Lumban Julu', 'Tampahan', 'Uluan', 'Siantar Narumonda',
    'Bontang Malayu', 'Habinsaran', 'Nassau', 'Pagaran', 'Siborong-Borong'
]

DESA_PER_KECAMATAN = 5

JENIS_LAYANAN = [
    'Pembuatan KTP Baru', 'Perpanjangan KTP', 'Pembuatan KK',
    'Perubahan KK', 'Akta Kelahiran', 'Akta Kematian',
    'Pindah Datang', 'Kartu Identitas Anak (KIA)',
    'Surat Pindah', 'Surat Keterangan', 'KIA Digital'
]

STATUS_ANTRIAN = [
    'Menunggu', 'Dokumen Diterima', 'Verifikasi Data',
    'Proses Cetak', 'Siap Pengambilan', 'Selesai', 'Ditolak'
]

ALASAN_DITOLAK = [
    'Data tidak lengkap', 'NIK tidak valid', 'Dokumen kurang jelas',
    'Bukan domisili', 'Duplikasi permohonan'
]

# Name generator fallback
NAMA_DEPAN_L = ['Budi', 'Andi', 'Joko', 'Agus', 'Made', 'Wayan', 'Putu', 'Ketut',
                 'Rudi', 'Dedi', 'Rian', 'Dimas', 'Rizky', 'Bayu', 'Fajar', 'Iwan']
NAMA_DEPAN_P = ['Siti', 'Dewi', 'Putri', 'Rina', 'Maya', 'Sari', 'Wulan', 'Lestari',
                 'Ani', 'Ratna', 'Indah', 'Ayus', 'Dina', 'Citra', 'Lina', 'Tika']
NAMA_BELAKANG = ['Santoso', 'Wijaya', 'Kusuma', 'Pratama', 'Hidayat', 'Saputra',
                  'Nugroho', 'Rahardjo', 'Wibowo', 'Suryadi', 'Permana', 'Setiawan',
                  'Gunawan', 'Firmansyah', 'Utami', 'Yuliarti']

def generate_nik():
    """Generate NIK 16 digit (provinsi Sumut + random)"""
    # Kode provinsi Sumatera Utara: 12-14
    provinsi = random.choice(['12', '13', '14'])
    # Kabupaten/Kota (2 digit)
    kabupaten = str(random.randint(1, 99)).zfill(2)
    # Kecamatan (2 digit)
    kecamatan = str(random.randint(1, 99)).zfill(2)
    # Nomor urut (4 digit)
    nomor_urut = str(random.randint(1, 9999)).zfill(4)
    # Kode unik (4 digit untuk cek checksum sederhana)
    kode_unik = str(random.randint(1, 9999)).zfill(4)

    return f"{provinsi}{kabupaten}{kecamatan}{nomor_urut}{kode_unik}"

def generate_nama(jenis_kelamin):
    """Generate nama Indonesia"""
    if jenis_kelamin == 'L':
        depan = random.choice(NAMA_DEPAN_L)
    else:
        depan = random.choice(NAMA_DEPAN_P)

    tengah = random.choice(['', ''])
    belakang = random.choice(NAMA_BELAKANG)

    return f"{depan} {tengah} {belakang}".strip()

def generate_no_hp():
    """Generate nomor HP Indonesia"""
    prefix = random.choice(['0812', '0813', '0821', '0822', '0852', '0853', '0857', '0858', '0877', '0878'])
    suffix = ''.join([str(random.randint(0, 9)) for _ in range(8)])
    return f"{prefix}{suffix}"

def generate_antrian(n=50000, start_year=2020, end_year=2024):
    """
    Generate data antrian online

    Args:
        n: Jumlah record
        start_year: Tahun mulai
        end_year: Tahun akhir

    Returns:
        pandas.DataFrame
    """
    print(f"Generating {n:,} antrian records...")

    data = []
    statuses_count = {status: 0 for status in STATUS_ANTRIAN}

    for i in range(n):
        # Tanggal random dalam rentang tahun
        total_days = (datetime(end_year, 12, 31) - datetime(start_year, 1, 1)).days
        days_ago = random.randint(0, total_days)
        created_at = datetime(start_year, 1, 1) + timedelta(days=days_ago)

        # Waktu dalam jam kerja (8-16)
        hour = random.randint(8, 16)
        minute = random.randint(0, 59)
        created_at = created_at.replace(hour=hour, minute=minute)

        # Jenis kelamin
        jk = random.choice(['L', 'P'])
        nama = generate_nama(jk) if not HAS_FAKER else fake.name()

        # Status dengan distribusi tidak merata (lebih banyak selesai)
        status_weights = [5, 10, 15, 10, 5, 50, 5]  # Bobot untuk setiap status
        status = random.choices(STATUS_ANTRIAN, weights=status_weights)[0]

        # Processing time berdasarkan status
        if status == 'Menunggu':
            processed_at = None
            processing_hours = 0
        elif status in ['Selesai', 'Siap Pengambilan']:
            processing_hours = random.randint(4, 72)
            processed_at = created_at + timedelta(hours=processing_hours)
        elif status == 'Ditolak':
            processing_hours = random.randint(2, 48)
            processed_at = created_at + timedelta(hours=processing_hours)
        else:
            processing_hours = random.randint(1, 24)
            processed_at = created_at + timedelta(hours=processing_hours)

        # OCR confidence (beberapa low untuk anomaly detection)
        if random.random() < 0.05:  # 5% low confidence
            ocr_confidence = round(random.uniform(0.50, 0.75), 4)
        else:
            ocr_confidence = round(random.uniform(0.85, 0.99), 4)

        record = {
            'id': str(i + 1).zfill(36),  # Simple ID
            'nomor_antrian': f"A{created_at.strftime('%Y%m%d')}{str(random.randint(1000, 9999))}",
            'nama_lengkap': nama,
            'nik': generate_nik(),
            'no_hp': generate_no_hp(),
            'jenis_layanan': random.choice(JENIS_LAYANAN),
            'kecamatan': random.choice(KECAMATAN_TOBA),
            'status': status,
            'file_ktp_path': f'ocr/uploads/ktp_{i}.jpg' if random.random() > 0.1 else None,
            'ocr_raw_text': f'NIK : {generate_nik()}\nNama : {nama}\n...' if random.random() > 0.2 else None,
            'ocr_confidence': ocr_confidence,
            'created_at': created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'processed_at': processed_at.strftime('%Y-%m-%d %H:%M:%S') if processed_at else None,
            'processing_hours': processing_hours,
            'alasan_ditolak': random.choice(ALASAN_DITOLAK) if status == 'Ditolak' else None
        }

        data.append(record)
        statuses_count[status] += 1

        # Progress
        if (i + 1) % 10000 == 0:
            print(f"  Generated {i + 1:,}/{n:,} records...")

    print(f"\nStatus distribution:")
    for status, count in statuses_count.items():
        print(f"  {status}: {count:,} ({count/n*100:.1f}%)")

    return pd.DataFrame(data)

def generate_statistik_penduduk(n=30000, start_year=2020, end_year=2024):
    """
    Generate data statistik penduduk per kecamatan

    Args:
        n: Jumlah record target
        start_year: Tahun mulai
        end_year: Tahun akhir

    Returns:
        pandas.DataFrame
    """
    print(f"\nGenerating statistik penduduk records...")

    data = []

    for year in range(start_year, end_year + 1):
        for month in range(1, 13):
            for kecamatan in KECAMATAN_TOBA:
                # Generate desa untuk setiap kecamatan
                for desa_idx in range(1, DESA_PER_KECAMATAN + 1):
                    desa = f"Desa {desa_idx} {kecamatan}"

                    # Data demografi
                    for jk in ['L', 'P']:
                        for umur_range in ['0-17', '18-30', '31-50', '51-70', '70+']:
                            # Random jumlah dengan variasi
                            base_jumlah = random.randint(50, 500)

                            # Variasi per tahun (growth)
                            growth = (year - start_year) * random.randint(-5, 20)
                            jumlah = max(10, base_jumlah + growth)

                            data.append({
                                'kecamatan': kecamatan,
                                'desa': desa,
                                'jenis_kelamin': jk,
                                'umur_range': umur_range,
                                'jumlah': jumlah,
                                'tahun': year,
                                'bulan': month
                            })

    df = pd.DataFrame(data)

    # Limit to n records if specified
    if n and len(df) > n:
        df = df.sample(n=n, random_state=42).reset_index(drop=True)

    print(f"Generated {len(df):,} statistik penduduk records")

    return df

def generate_statistik_dokumen(n=20000, start_year=2020, end_year=2024):
    """
    Generate data statistik penerbitan dokumen

    Returns:
        pandas.DataFrame
    """
    print(f"\nGenerating statistik dokumen records...")

    data = []

    jenis_dokumen = ['KTP-el', 'KIA', 'Akta Kelahiran', 'Akta Kematiah', 'KK', 'SKPWNI']

    for year in range(start_year, end_year + 1):
        for month in range(1, 13):
            for kecamatan in KECAMATAN_TOBA:
                for dokumen in jenis_dokumen:
                    # Random jumlah dengan variasi musiman
                    base_jumlah = random.randint(50, 300)

                    # Variasi per bulan (lebih tinggi di awal tahun)
                    seasonal = 1.0
                    if month in [1, 2, 3]:  # Awal tahun lebih ramai
                        seasonal = 1.2
                    elif month in [11, 12]:  # Akhir tahun lebih sepi
                        seasonal = 0.8

                    jumlah = int(base_jumlah * seasonal)

                    data.append({
                        'kecamatan': kecamatan,
                        'jenis_dokumen': dokumen,
                        'jumlah_diterbitkan': jumlah,
                        'jumlah_ditolak': random.randint(0, 20),
                        'tahun': year,
                        'bulan': month
                    })

    df = pd.DataFrame(data)

    # Limit to n records if specified
    if n and len(df) > n:
        df = df.sample(n=n, random_state=42).reset_index(drop=True)

    print(f"Generated {len(df):,} statistik dokumen records")

    return df

def generate_ocr_logs(n=20000, start_year=2020, end_year=2024):
    """
    Generate log proses OCR

    Returns:
        pandas.DataFrame
    """
    print(f"\nGenerating OCR log records...")

    data = []

    for i in range(n):
        total_days = (datetime(end_year, 12, 31) - datetime(start_year, 1, 1)).days
        days_ago = random.randint(0, total_days)
        created_at = datetime(start_year, 1, 1) + timedelta(days=days_ago)

        rotation_detected = random.choice([0, 0, 0, 90, 180, 270])
        quality_score = round(random.uniform(0.5, 0.99), 2)

        # Processing time based on rotation
        base_time = random.uniform(1.5, 5.0)
        if rotation_detected != 0:
            base_time += 1.0
        processing_time_ms = int(base_time * 1000)

        data.append({
            'log_id': str(i + 1).zfill(36),
            'antrian_id': str(random.randint(1, 50000)),
            'image_path': f'ocr/uploads/ktp_{random.randint(1, 50000)}.jpg',
            'rotation_detected': rotation_detected,
            'quality_score': quality_score,
            'processing_time_ms': processing_time_ms,
            'model_version': '1.0',
            'created_at': created_at.strftime('%Y-%m-%d %H:%M:%S')
        })

        if (i + 1) % 5000 == 0:
            print(f"  Generated {i + 1:,}/{n:,} log records...")

    print(f"Generated {len(df):,} OCR log records")

    return pd.DataFrame(data)

def main():
    parser = argparse.ArgumentParser(description="Big Data Generator for Disdukcapil")
    parser.add_argument('--records', type=int, default=100000, help='Total target records')
    parser.add_argument('--output', type=str, default='data/bigdata', help='Output directory')
    parser.add_argument('--format', type=str, default='both', choices=['csv', 'parquet', 'both'], help='Output format')

    args = parser.parse_args()

    # Create output directory
    output_dir = Path(args.output)
    output_dir.mkdir(parents=True, exist_ok=True)

    print("=" * 60)
    print("Big Data Generator - Disdukcapil Project")
    print("=" * 60)
    print(f"Target records: {args.records:,}")
    print(f"Output directory: {output_dir}")
    print("=" * 60)

    # Calculate record distribution
    antrian_records = int(args.records * 0.50)  # 50% antrian
    statistik_records = int(args.records * 0.30)  # 30% statistik
    dokumen_records = int(args.records * 0.20)  # 20% dokumen

    # Generate datasets
    antrian_df = generate_antrian(antrian_records)
    statistik_df = generate_statistik_penduduk(statistik_records)
    dokumen_df = generate_statistik_dokumen(dokumen_records)

    total_generated = len(antrian_df) + len(statistik_df) + len(dokumen_df)

    # Export based on format
    print(f"\nExporting data...")

    if args.format in ['csv', 'both']:
        print("\n[CSV Export]")
        antrian_df.to_csv(output_dir / 'antrian_online.csv', index=False)
        print(f"  Saved: antrian_online.csv ({len(antrian_df):,} records)")

        statistik_df.to_csv(output_dir / 'statistik_penduduk.csv', index=False)
        print(f"  Saved: statistik_penduduk.csv ({len(statistik_df):,} records)")

        dokumen_df.to_csv(output_dir / 'statistik_dokumen.csv', index=False)
        print(f"  Saved: statistik_dokumen.csv ({len(dokumen_df):,} records)")

    if args.format in ['parquet', 'both']:
        print("\n[Parquet Export]")
        antrian_df.to_parquet(output_dir / 'antrian_online.parquet', index=False)
        print(f"  Saved: antrian_online.parquet ({len(antrian_df):,} records)")

        statistik_df.to_parquet(output_dir / 'statistik_penduduk.parquet', index=False)
        print(f"  Saved: statistik_penduduk.parquet ({len(statistik_df):,} records)")

        dokumen_df.to_parquet(output_dir / 'statistik_dokumen.parquet', index=False)
        print(f"  Saved: statistik_dokumen.parquet ({len(dokumen_df):,} records)")

    print("\n" + "=" * 60)
    print(f"Generation Complete!")
    print(f"Total records generated: {total_generated:,}")
    print(f"Output directory: {output_dir.absolute()}")
    print("=" * 60)

    # Summary
    print("\nSummary:")
    print(f"  antrian_online:     {len(antrian_df):,} records")
    print(f"  statistik_penduduk: {len(statistik_df):,} records")
    print(f"  statistik_dokumen:  {len(dokumen_df):,} records")
    print(f"  TOTAL:              {total_generated:,} records")
    print()

    # Data quality check
    print("Data Quality Check:")
    print(f"  Null values in antrian: {antrian_df.isnull().sum().sum()}")
    print(f"  Duplicate rows: {antrian_df.duplicated().sum()}")
    print(f"  Memory usage: {antrian_df.memory_usage(deep=True).sum() / 1024**2:.2f} MB")

if __name__ == '__main__':
    main()
