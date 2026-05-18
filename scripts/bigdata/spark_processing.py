#!/usr/bin/env python3
"""
Apache Spark Processing Script untuk Big Data Project
Disdukcapil Kabupaten Toba

Fungsi:
1. Load data dari CSV/Parquet
2. Data cleaning (deduplikasi, validasi)
3. Data transformation
4. Aggregation dan analytics
5. Anomaly detection
6. Export hasil

Usage:
    spark-submit spark_processing.py --input data/bigdata --output data/bigdata/results
    python spark_processing.py --local
"""

import os
import sys
import argparse
import logging
from datetime import datetime
from pathlib import Path

# Check if running in Spark mode or local mode
SPARK_MODE = os.getenv('SPARK_MODE', 'false').lower() == 'true'

if SPARK_MODE:
    try:
        from pyspark.sql import SparkSession
        from pyspark.sql.functions import (
            col, count, avg, sum, when, to_date, year, month, dayofmonth,
            upper, trim, regexp_replace, udf, lit, stddev, variance, min, max,
            datediff, coalesce, floor, rand, desc, asc
        )
        from pyspark.sql.types import (
            StructType, StructField, StringType, IntegerType, FloatType,
            DoubleType, TimestampType, DateType, LongType
        )
        from pyspark.sql.window import Window
        HAS_SPARK = True
    except ImportError as e:
        print(f"Spark not available: {e}")
        print("Run: pip install pyspark")
        HAS_SPARK = False
        sys.exit(1)
else:
    # Local mode with pandas
    try:
        import pandas as pd
        import numpy as np
        HAS_SPARK = False
    except ImportError:
        print("Neither PySpark nor pandas available!")
        sys.exit(1)

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Constants
KECAMATAN_TOBA = [
    'Balige', 'Porsea', 'Laguboti', 'Borbor', 'Sigumpar',
    'Ajibata', 'Lumban Julu', 'Tampahan', 'Uluan', 'Siantar Narumondo',
    'Bontang Malayu', 'Habinsaran', 'Nassau', 'Pagaran', 'Siborong-Borong'
]

JENIS_LAYANAN = [
    'Pembuatan KTP Baru', 'Perpanjangan KTP', 'Pembuatan KK',
    'Perubahan KK', 'Akta Kelahiran', 'Akta Kematiah',
    'Pindah Datang', 'Kartu Identitas Anak (KIA)',
    'Surat Pindah', 'Surat Keterangan', 'KIA Digital'
]

STATUS_ANTRIAN = [
    'Menunggu', 'Dokumen Diterima', 'Verifikasi Data',
    'Proses Cetak', 'Siap Pengambilan', 'Selesai', 'Ditolak'
]


class BigDataProcessor:
    """Big Data Processor dengan Apache Spark atau Pandas"""

    def __init__(self, app_name="DisdukcapilBigData", local_mode=False):
        """Initialize processor"""
        self.local_mode = local_mode or not HAS_SPARK
        self.app_name = app_name

        if not self.local_mode:
            self.spark = SparkSession.builder \
                .appName(app_name) \
                .config("spark.sql.warehouse.dir", "spark-warehouse") \
                .config("spark.driver.memory", "4g") \
                .config("spark.executor.memory", "4g") \
                .config("spark.sql.adaptive.enabled", "true") \
                .config("spark.sql.adaptive.coalescePartitions.enabled", "true") \
                .getOrCreate()

            logger.info("Spark session created")
        else:
            logger.info("Running in local mode (pandas)")

    def load_data(self, path, format='csv'):
        """Load data dari file"""
        logger.info(f"Loading data from {path} (format: {format})")

        if not self.local_mode:
            return self._load_spark(path, format)
        else:
            return self._load_pandas(path, format)

    def _load_spark(self, path, format):
        """Load dengan PySpark"""
        if format == 'csv':
            df = self.spark.read.csv(
                path,
                header=True,
                inferSchema=True,
                timestampFormat='yyyy-MM-dd HH:mm:ss'
            )
        elif format == 'parquet':
            df = self.spark.read.parquet(path)
        elif format == 'json':
            df = self.spark.read.json(path)
        else:
            raise ValueError(f"Unsupported format: {format}")

        count = df.count()
        logger.info(f"Loaded {count:,} records with {len(df.columns)} columns")
        return df

    def _load_pandas(self, path, format):
        """Load dengan Pandas"""
        if format == 'csv':
            df = pd.read_csv(path)
        elif format == 'parquet':
            df = pd.read_parquet(path)
        elif format == 'json':
            df = pd.read_json(path)
        else:
            raise ValueError(f"Unsupported format: {format}")

        logger.info(f"Loaded {len(df):,} records with {len(df.columns)} columns")
        return df

    def clean_antrian_data(self, df):
        """
        Data cleaning untuk antrian data

        Steps:
        1. Remove duplicates
        2. Handle NULL values
        3. Standardize format (upper case, trim)
        4. Validate NIK (16 digit)
        5. Remove outliers
        """
        logger.info("Cleaning antrian data...")

        if not self.local_mode:
            return self._clean_antrian_spark(df)
        else:
            return self._clean_antrian_pandas(df)

    def _clean_antrian_spark(self, df):
        """Clean dengan PySpark"""
        original_count = df.count()

        # 1. Remove duplicates by ID
        df = df.dropDuplicates(['id'])

        # 2. Handle NULL values in critical fields
        df = df.filter(col('nik').isNotNull()) \
               .filter(col('nama_lengkap').isNotNull()) \
               .filter(col('jenis_layanan').isNotNull())

        # 3. Standardize format
        df = df.withColumn('nik', regexp_replace(col('nik'), '[^0-9]', '')) \
               .withColumn('nama_lengkap', upper(trim(col('nama_lengkap'))))
               .withColumn('jenis_layanan', trim(col('jenis_layanan')))

        # 4. Validate NIK (exactly 16 digits)
        df = df.filter(col('nik').rlike('^[0-9]{16}$'))

        # 5. Validate and filter outliers
        if 'processing_hours' in df.columns:
            df = df.filter(
                (col('processing_hours').isNull()) |
                ((col('processing_hours') >= 0) & (col('processing_hours') <= 168))  # Max 1 week
            )

        # 6. Filter invalid dates
        if 'created_at' in df.columns:
            df = df.filter(col('created_at').isNotNull())

        final_count = df.count()
        removed = original_count - final_count

        logger.info(f"Cleaning complete: {final_count:,} records (removed: {removed:,})")

        return df

    def _clean_antrian_pandas(self, df):
        """Clean dengan Pandas"""
        original_count = len(df)

        # 1. Remove duplicates
        df = df.drop_duplicates(subset=['id'])

        # 2. Handle NULL
        df = df.dropna(subset=['nik', 'nama_lengkap', 'jenis_layanan'])

        # 3. Standardize format
        df['nik'] = df['nik'].str.replace(r'[^0-9]', '', regex=True)
        df['nama_lengkap'] = df['nama_lengkap'].str.upper().str.strip()
        df['jenis_layanan'] = df['jenis_layanan'].str.strip()

        # 4. Validate NIK
        df = df[df['nik'].str.len() == 16]
        df = df[df['nik'].str.match(r'^[0-9]{16}$')]

        # 5. Filter outliers
        if 'processing_hours' in df.columns:
            df = df[(df['processing_hours'].isna()) | ((df['processing_hours'] >= 0) & (df['processing_hours'] <= 168))]

        final_count = len(df)
        removed = original_count - final_count

        logger.info(f"Cleaning complete: {final_count:,} records (removed: {removed:,})")

        return df

    def transform_statistik_penduduk(self, df):
        """
        Transformasi statistik penduduk

        Add computed columns:
        - periode (YYYY-MM)
        - kelompok_umur (category)
        - density (people per area)
        """
        logger.info("Transforming statistik penduduk...")

        if not self.local_mode:
            # Spark transformation
            df = df.withColumn('periode',
                              col('tahun').cast('string') + '-' +
                              col('bulan').cast('string').rjust(2, '0'))

            df = df.withColumn('kelompok_umur',
                              when(col('umur_range') == '0-17', 'Anak-Anak')
                              .when(col('umur_range') == '18-30', 'Muda')
                              .when(col('umur_range') == '31-50', 'Produktif')
                              .when(col('umur_range') == '51-70', 'Pra-Lansia')
                              .otherwise('Lansia'))
        else:
            # Pandas transformation
            df['periode'] = df['tahun'].astype(str) + '-' + df['bulan'].astype(str).str.zfill(2)

            umur_map = {
                '0-17': 'Anak-Anak',
                '18-30': 'Muda',
                '31-50': 'Produktif',
                '51-70': 'Pra-Lansia',
                '70+': 'Lansia'
            }
            df['kelompok_umur'] = df['umur_range'].map(umur_map)

        return df

    def aggregate_layanan_stats(self, df):
        """
        Aggregate statistik per jenis layanan

        Output columns:
        - jenis_layanan
        - total_permohonan
        - avg_confidence
        - total_selesai
        - total_ditolak
        - completion_rate
        """
        logger.info("Aggregating layanan statistics...")

        if not self.local_mode:
            result = df.groupBy('jenis_layanan') \
                .agg(
                    count('*').alias('total_permohonan'),
                    avg('ocr_confidence').alias('avg_confidence'),
                    sum(when(col('status') == 'Selesai', 1).otherwise(0)).alias('total_selesai'),
                    sum(when(col('status') == 'Ditolak', 1).otherwise(0)).alias('total_ditolak')
                ) \
                .withColumn('completion_rate',
                           col('total_selesai') / col('total_permohonan') * 100) \
                .orderBy(col('total_permohonan').desc())

            return result
        else:
            result = df.groupby('jenis_layanan').agg({
                'id': 'count',
                'ocr_confidence': 'mean',
            }).reset_index()
            result.columns = ['jenis_layanan', 'total_permohonan', 'avg_confidence']

            # Add completion stats
            selesai = df[df['status'] == 'Selesai'].groupby('jenis_layanan').size()
            ditolak = df[df['status'] == 'Ditolak'].groupby('jenis_layanan').size()

            result = result.merge(selesai.rename('total_selesai'), left_on='jenis_layanan', right_index=True, how='left')
            result = result.merge(ditolak.rename('total_ditolak'), left_on='jenis_layanan', right_index=True, how='left')

            result = result.fillna(0)
            result['completion_rate'] = (result['total_selesai'] / result['total_permohonan']) * 100

            return result.sort_values('total_permohonan', ascending=False)

    def aggregate_kecamatan_stats(self, df):
        """
        Aggregate statistik per kecamatan

        Output columns:
        - kecamatan
        - total_antrian
        - avg_confidence
        - total_selesai
        - avg_processing_time
        - completion_rate
        """
        logger.info("Aggregating kecamatan statistics...")

        if not self.local_mode:
            result = df.groupBy('kecamatan') \
                .agg(
                    count('*').alias('total_antrian'),
                    avg('ocr_confidence').alias('avg_confidence'),
                    avg('processing_hours').alias('avg_processing_time'),
                    sum(when(col('status') == 'Selesai', 1).otherwise(0)).alias('total_selesai')
                ) \
                .withColumn('completion_rate',
                           col('total_selesai') / col('total_antrian') * 100) \
                .orderBy(col('total_antrian').desc())

            return result
        else:
            result = df.groupby('kecamatan').agg({
                'id': 'count',
                'ocr_confidence': 'mean',
                'processing_hours': 'mean'
            }).reset_index()
            result.columns = ['kecamatan', 'total_antrian', 'avg_confidence', 'avg_processing_time']

            selesai = df[df['status'] == 'Selesai'].groupby('kecamatan').size()
            result = result.merge(selesai.rename('total_selesai'), left_on='kecamatan', right_index=True, how='left')
            result = result.fillna(0)
            result['completion_rate'] = (result['total_selesai'] / result['total_antrian']) * 100

            return result.sort_values('total_antrian', ascending=False)

    def temporal_analysis(self, df):
        """
        Analisis temporal (trend waktu)

        Output:
        - Monthly trend
        - Day of week pattern
        - Hour of day pattern
        """
        logger.info("Performing temporal analysis...")

        if not self.local_mode:
            # Extract time components
            df = df.withColumn('year', year(col('created_at'))) \
                   .withColumn('month', month(col('created_at'))) \
                   .withColumn('day_of_week', dayofmonth(col('created_at')))

            # Monthly trend
            monthly_trend = df.groupBy('year', 'month') \
                .agg(
                    count('*').alias('total_antrian'),
                    sum(when(col('status') == 'Selesai', 1).otherwise(0)).alias('total_selesai')
                ) \
                .orderBy('year', 'month')

            return monthly_trend
        else:
            df['created_at'] = pd.to_datetime(df['created_at'])
            df['year'] = df['created_at'].dt.year
            df['month'] = df['created_at'].dt.month
            df['day_of_week'] = df['created_at'].dt.dayofweek

            monthly_trend = df.groupby(['year', 'month']).agg({
                'id': 'count'
            }).reset_index()
            monthly_trend.columns = ['year', 'month', 'total_antrian']

            selesai = df[df['status'] == 'Selesai'].groupby(['year', 'month']).size()
            monthly_trend = monthly_trend.merge(
                selesai.rename('total_selesai'),
                left_on=['year', 'month'],
                right_index=True,
                how='left'
            ).fillna(0)

            return monthly_trend.sort_values(['year', 'month'])

    def detect_anomalies(self, df):
        """
        Deteksi anomaly dalam data

        Anomaly types:
        1. Low OCR confidence (< 70% of average)
        2. Very slow processing (> 3x average)
        3. Unusual patterns (statistical outliers)
        """
        logger.info("Detecting anomalies...")

        if not self.local_mode:
            # Calculate average confidence
            stats = df.agg(
                avg('ocr_confidence').alias('avg_conf'),
                avg('processing_hours').alias('avg_time')
            ).collect()[0]

            avg_conf = stats['avg_conf'] or 0.85
            avg_time = stats['avg_time'] or 24

            # Flag anomalies
            anomalies = df.filter(
                (col('ocr_confidence') < avg_conf * 0.7) |
                (col('processing_hours') > avg_time * 3)
            )

            anomaly_count = anomalies.count()
            logger.info(f"Found {anomaly_count} anomalies")

            return anomalies
        else:
            # Pandas version
            avg_conf = df['ocr_confidence'].mean() or 0.85
            avg_time = df['processing_hours'].mean() or 24

            anomalies = df[
                (df['ocr_confidence'] < avg_conf * 0.7) |
                (df['processing_hours'] > avg_time * 3)
            ]

            logger.info(f"Found {len(anomalies)} anomalies")

            return anomalies

    def save_results(self, df, output_path, format='csv'):
        """Save hasil processing"""
        logger.info(f"Saving results to {output_path}")

        output_path = Path(output_path)
        output_path.parent.mkdir(parents=True, exist_ok=True)

        if not self.local_mode:
            if format == 'csv':
                df.write.csv(str(output_path), header=True, mode='overwrite')
            elif format == 'parquet':
                df.write.parquet(str(output_path), mode='overwrite')
            elif format == 'json':
                df.write.json(str(output_path), mode='overwrite')
        else:
            if format == 'csv':
                df.to_csv(output_path, index=False)
            elif format == 'parquet':
                df.to_parquet(output_path, index=False)
            elif format == 'json':
                df.to_json(output_path, orient='records')

        logger.info(f"Results saved to {output_path}")

    def get_summary_stats(self, df):
        """Get summary statistics"""
        logger.info("Computing summary statistics...")

        if not self.local_mode:
            total_count = df.count()

            stats = {
                'total_records': total_count,
                'columns': len(df.columns),
                'memory_usage': 'N/A'
            }

            # Numeric stats
            if 'ocr_confidence' in df.columns:
                conf_stats = df.select(
                    avg('ocr_confidence').alias('mean'),
                    stddev('ocr_confidence').alias('stddev'),
                    min('ocr_confidence').alias('min'),
                    max('ocr_confidence').alias('max')
                ).collect()[0]

                stats['ocr_confidence'] = {
                    'mean': float(conf_stats['mean']) if conf_stats['mean'] else 0,
                    'stddev': float(conf_stats['stddev']) if conf_stats['stddev'] else 0,
                    'min': float(conf_stats['min']) if conf_stats['min'] else 0,
                    'max': float(conf_stats['max']) if conf_stats['max'] else 0
                }
        else:
            stats = {
                'total_records': len(df),
                'columns': len(df.columns),
                'memory_usage_mb': df.memory_usage(deep=True).sum() / 1024**2
            }

            if 'ocr_confidence' in df.columns:
                stats['ocr_confidence'] = {
                    'mean': float(df['ocr_confidence'].mean()),
                    'stddev': float(df['ocr_confidence'].std()),
                    'min': float(df['ocr_confidence'].min()),
                    'max': float(df['ocr_confidence'].max())
                }

        return stats

    def stop(self):
        """Stop Spark session"""
        if not self.local_mode:
            self.spark.stop()
            logger.info("Spark session stopped")


def main():
    parser = argparse.ArgumentParser(description="Big Data Processing with Apache Spark")
    parser.add_argument('--input', type=str, default='data/bigdata', help='Input directory')
    parser.add_argument('--output', type=str, default='data/bigdata/results', help='Output directory')
    parser.add_argument('--local', action='store_true', help='Run in local mode (pandas)')
    parser.add_argument('--format', type=str, default='csv', choices=['csv', 'parquet'], help='Input format')
    parser.add_argument('--aggregate', action='store_true', help='Run aggregations')
    parser.add_argument('--anomalies', action='store_true', help='Detect anomalies')
    parser.add_argument('--all', action='store_true', help='Run all processing steps')

    args = parser.parse_args()

    print("=" * 60)
    print("Big Data Processor - Disdukcapil Project")
    print("=" * 60)
    print(f"Mode: {'Local (Pandas)' if args.local else 'Spark'}")
    print(f"Input: {args.input}")
    print(f"Output: {args.output}")
    print("=" * 60)
    print()

    # Initialize processor
    processor = BigDataProcessor(local_mode=args.local)

    try:
        # Load data
        antrian_path = f"{args.input}/antrian_online.{args.format}"
        antrian_df = processor.load_data(antrian_path, args.format)

        # Print summary
        summary = processor.get_summary_stats(antrian_df)
        print(f"Summary: {summary['total_records']:,} records")
        if 'ocr_confidence' in summary:
            oc = summary['ocr_confidence']
            print(f"  OCR Confidence: {oc['mean']:.4f} (±{oc['stddev']:.4f})")
        print()

        # Clean data
        if args.all or True:
            clean_df = processor.clean_antrian_data(antrian_df)

        # Aggregations
        if args.all or args.aggregate:
            print("\n=== Layanan Statistics ===")
            layanan_stats = processor.aggregate_layanan_stats(clean_df)

            if not args.local:
                layanan_stats.show(truncate=False)
            else:
                print(layanan_stats.to_string())

            # Save layanan stats
            processor.save_results(
                layanan_stats,
                f"{args.output}/layanan_stats.{args.format}",
                args.format
            )

            print("\n=== Kecamatan Statistics ===")
            kecamatan_stats = processor.aggregate_kecamatan_stats(clean_df)

            if not args.local:
                kecamatan_stats.show(truncate=False)
            else:
                print(kecamatan_stats.to_string())

            # Save kecamatan stats
            processor.save_results(
                kecamatan_stats,
                f"{args.output}/kecamatan_stats.{args.format}",
                args.format
            )

            print("\n=== Monthly Trend ===")
            monthly_trend = processor.temporal_analysis(clean_df)

            if not args.local:
                monthly_trend.show(24, truncate=False)
            else:
                print(monthly_trend.head(24).to_string())

            # Save monthly trend
            processor.save_results(
                monthly_trend,
                f"{args.output}/monthly_trend.{args.format}",
                args.format
            )

        # Anomaly detection
        if args.all or args.anomalies:
            print("\n=== Anomalies ===")
            anomalies = processor.detect_anomalies(clean_df)

            if not args.local:
                anomalies_count = anomalies.count()
                print(f"Total anomalies: {anomalies_count:,}")
                anomalies.show(10, truncate=False)
            else:
                print(f"Total anomalies: {len(anomalies):,}")
                print(anomalies.head(10).to_string())

            # Save anomalies
            processor.save_results(
                anomalies,
                f"{args.output}/anomalies.{args.format}",
                args.format
            )

        print("\n" + "=" * 60)
        print("Processing Complete!")
        print(f"Results saved to: {args.output}")
        print("=" * 60)

    except Exception as e:
        logger.error(f"Processing error: {e}")
        import traceback
        traceback.print_exc()

    finally:
        processor.stop()


if __name__ == '__main__':
    main()
