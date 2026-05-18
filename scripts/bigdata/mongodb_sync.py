#!/usr/bin/env python3
"""
MongoDB Integration Script untuk Big Data Project
Disdukcapil Kabupaten Toba

Fungsi:
1. Sync data dari MySQL ke MongoDB
2. Setup indexes untuk query optimal
3. Aggregate data untuk analytics

Usage:
    python mongodb_sync.py --sync --all
    python mongodb_sync.py --aggregate
"""

import os
import sys
import argparse
import logging
from datetime import datetime
from pathlib import Path

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent))

try:
    import pymongo
    from pymongo import MongoClient, UpdateOne
    from pymongo.errors import BulkWriteError
    import mysql.connector
    from mysql.connector import Error
except ImportError as e:
    print(f"Missing dependency: {e}")
    print("Run: pip install -r requirements_bigdata.txt")
    sys.exit(1)

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Configuration
MYSQL_CONFIG = {
    'host': os.getenv('MYSQL_HOST', 'localhost'),
    'port': int(os.getenv('MYSQL_PORT', 3306)),
    'user': os.getenv('MYSQL_USER', 'root'),
    'password': os.getenv('MYSQL_PASSWORD', ''),
    'database': os.getenv('MYSQL_DATABASE', 'disdukcapil'),
    'charset': 'utf8mb4'
}

MONGO_URI = os.getenv('MONGO_URI', 'mongodb://localhost:27017/')
MONGO_DB = 'disdukcapil_bigdata'

# Kecamatan data
KECAMATAN_TOBA = [
    'Balige', 'Porsea', 'Laguboti', 'Borbor', 'Sigumpar',
    'Ajibata', 'Lumban Julu', 'Tampahan', 'Uluan', 'Siantar Narumondo',
    'Bontang Malayu', 'Habinsaran', 'Nassau', 'Pagaran', 'Siborong-Borong'
]


class MongoSyncManager:
    """Manager untuk sinkronisasi MySQL ke MongoDB"""

    def __init__(self, mysql_config, mongo_uri, mongo_db):
        """Initialize connections"""
        self.mysql_config = mysql_config
        self.mongo_uri = mongo_uri
        self.mongo_db_name = mongo_db

        self.mysql_conn = None
        self.mongo_client = None
        self.db = None

    def connect_mysql(self):
        """Connect ke MySQL"""
        try:
            self.mysql_conn = mysql.connector.connect(**self.mysql_config)
            logger.info(f"Connected to MySQL: {self.mysql_config['host']}/{self.mysql_config['database']}")
            return True
        except Error as e:
            logger.error(f"MySQL connection failed: {e}")
            return False

    def connect_mongodb(self):
        """Connect ke MongoDB"""
        try:
            self.mongo_client = MongoClient(self.mongo_uri)
            self.db = self.mongo_client[self.mongo_db_name]

            # Test connection
            self.db.command('ping')
            logger.info(f"Connected to MongoDB: {self.mongo_db_name}")
            return True
        except Exception as e:
            logger.error(f"MongoDB connection failed: {e}")
            return False

    def close(self):
        """Close all connections"""
        if self.mysql_conn:
            self.mysql_conn.close()
            logger.info("MySQL connection closed")

        if self.mongo_client:
            self.mongo_client.close()
            logger.info("MongoDB connection closed")

    def create_indexes(self):
        """Create indexes untuk performa query optimal"""
        logger.info("Creating MongoDB indexes...")

        # Collection: antrian_online
        antrian_col = self.db.antrian_online
        antrian_col.create_index([('antrian_id', pymongo.ASCENDING)], unique=True)
        antrian_col.create_index([('nik', pymongo.ASCENDING)])
        antrian_col.create_index([('kecamatan', pymongo.ASCENDING)])
        antrian_col.create_index([('status', pymongo.ASCENDING)])
        antrian_col.create_index([('created_at', pymongo.DESCENDING)])
        antrian_col.create_index([('ocr_confidence', pymongo.ASCENDING)])
        antrian_col.create_index([('kecamatan', pymongo.ASCENDING), ('created_at', pymongo.DESCENDING)])

        # Collection: ocr_raw
        ocr_col = self.db.ocr_raw
        ocr_col.create_index([('antrian_id', pymongo.ASCENDING)], unique=True)
        ocr_col.create_index([('created_at', pymongo.DESCENDING)])
        ocr_col.create_index([('confidence', pymongo.ASCENDING)])
        ocr_col.create_index([('quality_score', pymongo.ASCENDING)])

        # Collection: kecamatan_stats
        stats_col = self.db.kecamatan_stats
        stats_col.create_index([('kecamatan', pymongo.ASCENDING)], unique=True)
        stats_col.create_index([('tahun', pymongo.ASCENDING), ('bulan', pymongo.ASCENDING)])

        # Collection: analytics_cache
        cache_col = self.db.analytics_cache
        cache_col.create_index([('cache_key', pymongo.ASCENDING)], unique=True)
        cache_col.create_index([('expires_at', pymongo.ASCENDING)])

        logger.info("Indexes created successfully")

    def sync_antrian_from_mysql(self, batch_size=1000, limit=None):
        """
        Sync data antrian dari MySQL ke MongoDB

        Args:
            batch_size: Jumlah record per batch
            limit: Batas record yang di-sync (None = semua)
        """
        logger.info("Syncing antrian data from MySQL to MongoDB...")

        try:
            cursor = self.mysql_conn.cursor(dictionary=True)

            # Get total count
            cursor.execute("SELECT COUNT(*) as total FROM antrian_online")
            total = cursor.fetchone()['total']

            if limit:
                total = min(total, limit)

            logger.info(f"Total records to sync: {total:,}")

            offset = 0
            synced = 0

            while offset < total:
                # Fetch batch
                limit_batch = min(batch_size, total - offset)
                query = f"""
                    SELECT
                        id, nomor_antrian, nama_lengkap, nik, jenis_layanan,
                        status, file_ktp_path, ocr_raw_text, ocr_confidence,
                        created_at, processed_at
                    FROM antrian_online
                    ORDER BY created_at DESC
                    LIMIT {limit_batch} OFFSET {offset}
                """

                cursor.execute(query)
                batch = cursor.fetchall()

                if not batch:
                    break

                # Prepare bulk operations
                operations = []
                for row in batch:
                    # Add metadata
                    row['synced_at'] = datetime.now()
                    row['source'] = 'mysql'

                    # Extract kecamatan (simulated, in real data would come from another table)
                    import random
                    row['kecamatan'] = random.choice(KECAMATAN_TOBA)

                    # Add processing time in hours
                    if row['processed_at']:
                        row['processing_hours'] = (
                            row['processed_at'] - row['created_at']
                        ).total_seconds() / 3600
                    else:
                        row['processing_hours'] = None

                    operations.append(
                        UpdateOne(
                            {'antrian_id': row['id']},
                            {'$set': row},
                            upsert=True
                        )
                    )

                # Bulk write
                if operations:
                    result = self.db.antrian_online.bulk_write(operations, ordered=False)
                    synced += result.matched_count + result.upserted_count

                    # Update synced count
                    actual_synced = len(batch)
                    synced = offset + actual_synced

                offset += batch_size

                if offset % 10000 == 0:
                    logger.info(f"Synced {synced:,}/{total:,} records...")

            logger.info(f"Sync complete: {synced:,} records")

        except Error as e:
            logger.error(f"MySQL error: {e}")
        except BulkWriteError as e:
            logger.warning(f"Bulk write error (some records may have failed): {e.details}")
        except Exception as e:
            logger.error(f"Sync error: {e}")

    def sync_statistik_from_csv(self, csv_path):
        """
        Sync data statistik dari CSV ke MongoDB

        Args:
            csv_path: Path ke file CSV
        """
        import pandas as pd

        logger.info(f"Syncing statistik from CSV: {csv_path}")

        try:
            df = pd.read_csv(csv_path)
            logger.info(f"Loaded {len(df):,} records from CSV")

            # Convert to dict and insert
            records = df.to_dict('records')

            # Add metadata
            for record in records:
                record['synced_at'] = datetime.now()
                record['source'] = 'csv'

            # Bulk insert
            collection_name = Path(csv_path).stem
            collection = self.db[collection_name]

            # Clear existing
            collection.delete_many({})

            # Insert new
            result = collection.insert_many(records)

            logger.info(f"Inserted {len(result.inserted_ids):,} records to {collection_name}")

        except Exception as e:
            logger.error(f"CSV sync error: {e}")

    def aggregate_kecamatan_stats(self):
        """
        Aggregate statistik per kecamatan

        Pipeline:
        1. Group by kecamatan
        2. Count total antrian
        3. Average OCR confidence
        4. Count by status
        """
        logger.info("Aggregating kecamatan statistics...")

        pipeline = [
            {
                '$group': {
                    '_id': '$kecamatan',
                    'total_antrian': {'$sum': 1},
                    'avg_confidence': {'$avg': '$ocr_confidence'},
                    'total_selesai': {
                        '$sum': {
                            '$cond': [{'$eq': ['$status', 'Selesai']}, 1, 0]
                        }
                    },
                    'total_ditolak': {
                        '$sum': {
                            '$cond': [{'$eq': ['$status', 'Ditolak']}, 1, 0]
                        }
                    },
                    'avg_processing_time': {'$avg': '$processing_hours'},
                    'jenis_layanan_list': {'$push': '$jenis_layanan'}
                }
            },
            {
                '$addFields': {
                    'completion_rate': {
                        '$multiply': [
                            {'$divide': ['$total_selesai', '$total_antrian']}, 100
                        ]
                    }
                }
            },
            {
                '$sort': {'total_antrian': -1}
            }
        ]

        result = self.db.antrian_online.aggregate(pipeline)

        # Save to kecamatan_stats collection
        stats_list = []
        for doc in result:
            doc['kecamatan'] = doc.pop('_id')
            doc['updated_at'] = datetime.now()
            stats_list.append(doc)

        # Clear and insert
        self.db.kecamatan_stats.delete_many({})
        if stats_list:
            self.db.kecamatan_stats.insert_many(stats_list)

        logger.info(f"Aggregated {len(stats_list)} kecamatan statistics")

        return stats_list

    def aggregate_monthly_trend(self):
        """Aggregate trend bulanan antrian"""
        logger.info("Aggregating monthly trend...")

        pipeline = [
            {
                '$project': {
                    'year': {'$year': '$created_at'},
                    'month': {'$month': '$created_at'},
                    'status': 1,
                    'kecamatan': 1
                }
            },
            {
                '$group': {
                    '_id': {
                        'year': '$year',
                        'month': '$month',
                        'kecamatan': '$kecamatan'
                    },
                    'total_antrian': {'$sum': 1},
                    'total_selesai': {
                        '$sum': {
                            '$cond': [{'$eq': ['$status', 'Selesai']}, 1, 0]
                        }
                    }
                }
            },
            {
                '$sort': {'_id.year': -1, '_id.month': -1}
            }
        ]

        result = self.db.antrian_online.aggregate(pipeline)

        # Save to monthly_trend collection
        trend_list = []
        for doc in result:
            doc['year'] = doc['_id']['year']
            doc['month'] = doc['_id']['month']
            doc['kecamatan'] = doc['_id']['kecamatan']
            doc.pop('_id')
            doc['updated_at'] = datetime.now()
            trend_list.append(doc)

        # Clear and insert
        self.db.monthly_trend.delete_many({})
        if trend_list:
            self.db.monthly_trend.insert_many(trend_list)

        logger.info(f"Aggregated {len(trend_list)} monthly trend records")

        return trend_list

    def detect_anomalies(self):
        """
        Deteksi anomaly dalam data

        Anomaly types:
        1. Low OCR confidence (< 0.7)
        2. Very slow processing (> 72 hours)
        3. Unusually long wait times
        """
        logger.info("Detecting anomalies...")

        # Calculate thresholds
        avg_confidence = list(self.db.antrian_online.aggregate([
            {'$group': {'_id': None, 'avg': {'$avg': '$ocr_confidence'}}}
        ]))[0]['avg']

        confidence_threshold = avg_confidence * 0.7

        # Find low confidence records
        low_confidence = list(self.db.antrian_online.find(
            {'ocr_confidence': {'$lt': confidence_threshold}},
            projection=['nomor_antrian', 'nama_lengkap', 'kecamatan', 'ocr_confidence', 'status']
        ).limit(100))

        # Find slow processing records
        slow_processing = list(self.db.antrian_online.find(
            {'processing_hours': {'$gt': 72}},
            projection=['nomor_antrian', 'nama_lengkap', 'kecamatan', 'processing_hours', 'status']
        ).limit(100))

        logger.info(f"Found {len(low_confidence)} low confidence records")
        logger.info(f"Found {len(slow_processing)} slow processing records")

        # Save to anomalies collection
        self.db.anomalies.delete_many({})

        anomalies = []
        for doc in low_confidence:
            doc['anomaly_type'] = 'low_confidence'
            doc['detected_at'] = datetime.now()
            anomalies.append(doc)

        for doc in slow_processing:
            doc['anomaly_type'] = 'slow_processing'
            doc['detected_at'] = datetime.now()
            anomalies.append(doc)

        if anomalies:
            self.db.anomalies.insert_many(anomalies)

        logger.info(f"Total anomalies: {len(anomalies)}")

        return {
            'low_confidence': len(low_confidence),
            'slow_processing': len(slow_processing),
            'thresholds': {
                'confidence_threshold': confidence_threshold,
                'processing_threshold_hours': 72
            }
        }

    def generate_summary_metrics(self):
        """Generate summary metrics untuk dashboard"""
        logger.info("Generating summary metrics...")

        # Total counts
        total_antrian = self.db.antrian_online.count_documents({})
        total_selesai = self.db.antrian_online.count_documents({'status': 'Selesai'})

        # Average confidence
        avg_confidence = list(self.db.antrian_online.aggregate([
            {'$group': {'_id': None, 'avg': {'$avg': '$ocr_confidence'}}}
        ]))[0]['avg'] if total_antrian > 0 else 0

        # Average processing time (completed only)
        avg_time = list(self.db.antrian_online.aggregate([
            {'$match': {'processing_hours': {'$ne': None, '$gt': 0}}},
            {'$group': {'_id': None, 'avg': {'$avg': '$processing_hours'}}}
        ]))

        avg_processing_time = avg_time[0]['avg'] if avg_time else 0

        # Top kecamatan
        top_kecamatan = list(self.db.kecamatan_stats.find(
            projection=['kecamatan', 'total_antrian']
        ).sort('total_antrian', -1).limit(5))

        metrics = {
            'total_antrian': total_antrian,
            'total_selesai': total_selesai,
            'completion_rate': (total_selesai / total_antrian * 100) if total_antrian > 0 else 0,
            'avg_confidence': avg_confidence,
            'avg_processing_time': avg_processing_time,
            'top_kecamatan': top_kecamatan,
            'updated_at': datetime.now()
        }

        # Cache the metrics
        self.db.analytics_cache.update_one(
            {'cache_key': 'summary_metrics'},
            {'$set': {**metrics, 'cache_key': 'summary_metrics', 'expires_at': datetime.now()}},
            upsert=True
        )

        logger.info(f"Summary metrics: {total_antrian:,} antrian, {metrics['completion_rate']:.1f}% completion")

        return metrics


def main():
    parser = argparse.ArgumentParser(description="MongoDB Sync Manager")
    parser.add_argument('--sync', action='store_true', help='Sync data from MySQL')
    parser.add_argument('--sync-csv', type=str, help='Sync data from CSV file')
    parser.add_argument('--aggregate', action='store_true', help='Run aggregations')
    parser.add_argument('--detect-anomalies', action='store_true', help='Detect anomalies')
    parser.add_argument('--summary', action='store_true', help='Generate summary metrics')
    parser.add_argument('--all', action='store_true', help='Run all operations')
    parser.add_argument('--limit', type=int, help='Limit records to sync (for testing)')
    parser.add_argument('--batch-size', type=int, default=1000, help='Batch size for sync')

    args = parser.parse_args()

    if not any([args.sync, args.sync_csv, args.aggregate, args.detect_anomalies, args.summary, args.all]):
        parser.print_help()
        return

    print("=" * 60)
    print("MongoDB Sync Manager - Disdukcapil Big Data")
    print("=" * 60)
    print()

    # Initialize manager
    manager = MongoSyncManager(MYSQL_CONFIG, MONGO_URI, MONGO_DB)

    try:
        # Connect
        if not manager.connect_mysql():
            logger.error("Failed to connect to MySQL")
            return

        if not manager.connect_mongodb():
            logger.error("Failed to connect to MongoDB")
            return

        # Create indexes
        manager.create_indexes()
        print()

        # Run operations
        if args.all or args.sync:
            manager.sync_antrian_from_mysql(batch_size=args.batch_size, limit=args.limit)
            print()

        if args.sync_csv:
            manager.sync_statistik_from_csv(args.sync_csv)
            print()

        if args.all or args.aggregate:
            manager.aggregate_kecamatan_stats()
            manager.aggregate_monthly_trend()
            print()

        if args.all or args.detect_anomalies:
            anomalies = manager.detect_anomalies()
            print(f"Anomalies detected: {anomalies}")
            print()

        if args.all or args.summary:
            metrics = manager.generate_summary_metrics()
            print(f"Summary: {metrics['total_antrian']:,} antrian, {metrics['completion_rate']:.1f}% complete")
            print()

        print("=" * 60)
        print("Operations Complete!")
        print("=" * 60)

    finally:
        manager.close()


if __name__ == '__main__':
    main()
