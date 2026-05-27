"""
Fix encoding rusak di file blade.
Handles multi-byte UTF-8 sequences that Edit tool tidak bisa deteksi.
"""
import os
import re
from pathlib import Path

# Mapping karakter rusak ke replacements
# these are the raw bytes represented as escape sequences

REPLACEMENTS = [
    # Dash-like characters (em dash, en dash, etc)
    ('Ã¢Ã¯Â¿Â½Ã¯Â¿Â½', ' - '),
    ('ÃƒÂ¢Ã¯Â¿Â½Ã¯Â¿Â½', ' - '),
    ('Ã¢Ã¯Â¿Â½Ã¯Â¿Â½Ã¯Â¿Â½', ' - '),
    ('ÃƒÂ¢Ã¯Â¿Â½Ã¯Â¿Â½Ã¯Â¿Â½', ' - '),
    ('Ã¢Ã¯Â¿Â½Ã¯Â¿Â½', '-'),
    ('ÃƒÂ¢Ã¯Â¿Â½Ã¯Â¿Â½', '-'),
    # Quote-like sequences (em dash)
    ('Ã¢Ã¯Â¿Â½', ''),
    ('ÃƒÂ¢Ã¯Â¿Â½', ''),
    # Bullet-like
    ('Â¢', ''),
    ('Â½', ''),
    ('Â¿', ''),
    # Apostrophe-like
    ('Ã¢', ''),
    ('Ã©', 'e'),
    ('Ã¨', 'e'),
    ('Ã¢Ã¯Â¿Â½', ''),
    # Extra replacements for en/em dashes in JS labels
    ('KK Baru ÃƒÂ¢Ã¯Â¿Â½Ã¯Â¿Â½ Pasangan', 'KK Baru - Pasangan'),
    ('KK Baru ÃƒÂ¢Ã¯Â¿Â½Ã¯Â¿Â½ Ortu Pria', 'KK Baru - Ortu Pria'),
    ('KK Baru ÃƒÂ¢Ã¯Â¿Â½Ã¯Â¿Â½ Ortu Wanita', 'KK Baru - Ortu Wanita'),
    ('KK Baru - Pasangan', 'KK Baru - Pasangan'),
    ('KK Baru - Ortu Pria', 'KK Baru - Ortu Pria'),
    ('KK Baru - Ortu Wanita', 'KK Baru - Ortu Wanita'),
]

def fix_file(filepath):
    """Fix encoding dalam satu file."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except UnicodeDecodeError:
        try:
            with open(filepath, 'r', encoding='latin-1') as f:
                content = f.read()
        except Exception as e:
            print(f"  [SKIP] Cannot read {filepath}: {e}")
            return False

    original = content
    changed = False

    # Apply all replacements
    for bad, good in REPLACEMENTS:
        if bad in content:
            content = content.replace(bad, good)
            changed = True

    # Also clean up double spaces
    content = re.sub(r'  +', ' ', content)
    content = re.sub(r' -  - ', ' - ', content)

    if changed:
        try:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"  [FIXED] {filepath}")
            return True
        except Exception as e:
            print(f"  [ERROR] Cannot write {filepath}: {e}")
            return False

    return False

def main():
    views_dir = Path(r'd:\Semester 6\PA 3\Project\PA3\resources\views')

    files_to_check = [
        views_dir / 'pages' / 'antrian-online.blade.php',
        views_dir / 'admin' / 'penerbitan_pernikahan.blade.php',
        views_dir / 'keagamaan' / 'pernikahan.blade.php',
    ]

    print("Fixing encoding di 3 file tersisa...")
    for f in files_to_check:
        if f.exists():
            fix_file(f)
        else:
            print(f"  [NOT FOUND] {f}")

    print("\nVerifying residual bad chars...")
    bad_pattern = re.compile(r'Ã[Ã¢Âƒ]|â[€"¹]|Â[¢½¿]')
    for f in files_to_check:
        if f.exists():
            with open(f, 'r', encoding='utf-8', errors='ignore') as fh:
                content = fh.read()
            matches = bad_pattern.findall(content)
            if matches:
                print(f"  [STILL HAS] {f.name}: {set(matches)}")
            else:
                print(f"  [CLEAN] {f.name}")

if __name__ == '__main__':
    main()