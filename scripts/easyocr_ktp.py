#!/usr/bin/env python3
"""KTP OCR Service - Flask API + CLI Mode v4.0.

Primary recognizer: trained ktp_crnn_v2 PyTorch models.
Support/fallback: EasyOCR detection and recognition.
"""

import os
# Fix OpenMP duplicate library conflict (PyTorch + OpenCV bundle libiomp5md.dll)
os.environ['KMP_DUPLICATE_LIB_OK'] = 'TRUE'


def _env_bool(name, default=True):
    value = os.environ.get(name)
    if value is None:
        return default
    return value.strip().lower() not in {"0", "false", "no", "off"}


def _env_path(name, default):
    value = os.environ.get(name)
    if not value:
        return default
    path = value.strip()
    if os.path.isabs(path):
        return path
    return os.path.abspath(os.path.join(os.path.dirname(__file__), "..", path))

import sys, re, json, base64, io
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename

try:
    import cv2, numpy as np
    from PIL import Image
    IMAGE_LIBS_AVAILABLE = True
except ImportError as e:
    IMAGE_LIBS_AVAILABLE = False
    print(f"# Warning: {e}", file=sys.stderr)

try:
    import easyocr
    EASYOCR_AVAILABLE = True
except ImportError as e:
    EASYOCR_AVAILABLE = False
    print(f"# Warning: {e}", file=sys.stderr)

try:
    if not _env_bool("KTP_CRNN_ENABLED", True):
        raise RuntimeError("CRNN disabled by KTP_CRNN_ENABLED=false")

    from ktp_crnn_ocr import diagnose as diagnose_crnn
    from ktp_crnn_ocr import recognize_image as recognize_image_with_crnn
    CRNN_AVAILABLE = True
except Exception as e:
    CRNN_AVAILABLE = False
    diagnose_crnn = None
    recognize_image_with_crnn = None
    print(f"# Warning: custom CRNN module unavailable: {e}", file=sys.stderr)

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = "uploads"
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

_reader = None

def get_reader():
    global _reader
    if _reader is None and EASYOCR_AVAILABLE:
        model_dir = _env_path(
            "EASYOCR_MODEL_DIR",
            os.path.join(os.path.dirname(__file__), "models", "easyocr_models"),
        )
        try:
            _reader = easyocr.Reader(
                ["id", "en"],
                gpu=False,
                verbose=False,
                model_storage_directory=model_dir,
                download_enabled=_env_bool("EASYOCR_DOWNLOAD_ENABLED", False),
            )
        except TypeError:
            _reader = easyocr.Reader(["id", "en"], gpu=False, verbose=False)
        except Exception as e:
            print(f"# Warning: EasyOCR reader unavailable: {e}", file=sys.stderr)
            _reader = None
    return _reader

def allowed_file(fn):
    return "." in fn and fn.rsplit(".", 1)[1].lower() in {"png", "jpg", "jpeg", "webp", "bmp"}

def preprocess_image(path):
    if not IMAGE_LIBS_AVAILABLE: return path
    img = cv2.imread(path)
    if img is None: return path
    try:
        h, w = img.shape[:2]
        if w < 800:
            img = cv2.resize(img, None, fx=800/w, fy=800/w, interpolation=cv2.INTER_CUBIC)
        gray = cv2.cvtColor(img.copy(), cv2.COLOR_BGR2GRAY)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        enhanced = clahe.apply(gray)
        denoised = cv2.fastNlMeansDenoising(enhanced, None, h=5, templateWindowSize=7)
        out = path.replace(".", "_proc.")
        cv2.imwrite(out, denoised, [cv2.IMWRITE_PNG_COMPRESSION, 0])
        return out
    except: return path

class KtpOcrParser:
    def __init__(self, text):
        self.raw = text
        self.lines = [l.strip() for l in text.replace('\r\n','\n').replace('\r','\n').split('\n') if l.strip()]
        self.upper = [l.upper() for l in self.lines]

    def _clean_value(self, value):
        value = re.sub(r'\s+', ' ', value or '').strip(' :;,.|-/')
        return value.strip()

    def _ocr_digits(self, value):
        table = str.maketrans({
            'O': '0', 'o': '0', 'Q': '0', 'D': '0',
            'I': '1', 'l': '1', '|': '1', ']': '1', '[': '1',
            ')': '1', '(': '1', '!': '1',
            'S': '5', 's': '5',
            'B': '8',
        })
        return re.sub(r'\D+', '', (value or '').translate(table))

    def _birth_date_from_nik(self, nik):
        if not is_valid_nik(nik):
            return ''
        day = int(nik[6:8])
        if day > 40:
            day -= 40
        month = int(nik[8:10])
        year_two = int(nik[10:12])
        year = 1900 + year_two if year_two >= 30 else 2000 + year_two
        return f"{day:02d}-{month:02d}-{year}"

    def _find_idx_regex(self, patterns):
        for i, line in enumerate(self.upper):
            for pattern in patterns:
                if re.search(pattern, line, re.I):
                    return i
        return -1

    def _value_around_regex(self, line, patterns, prefer='auto'):
        original = line
        upper = line.upper()
        for pattern in patterns:
            match = re.search(pattern, upper, re.I)
            if not match:
                continue

            before = self._clean_value(original[:match.start()])
            after = self._clean_value(original[match.end():])
            before = self._remove_known_labels(before)
            after = self._remove_known_labels(after)

            if prefer == 'before' and before:
                return before
            if prefer == 'after' and after:
                return after
            if before and not after:
                return before
            if after and not before:
                return after
            if before and after:
                if len(before) >= 3 and not re.fullmatch(r'[\d\s/.-]+', before):
                    return before
                return after
        return ''

    def _remove_known_labels(self, value):
        value = re.sub(
            r'\b(NIK|HIK|NAMA|NAN|NAMN|NARN|CAM|TEMPAT|TEMPATTGL|TEPA\w*|TEMP\w*|TGL|LAHIR|LAHR|JENIS|KELAMIN|GOL|DARAH|'
            r'ALAMAT|MLAMAR|NAMAT|KEL|KELDESA|KEDESE|KET/DER|KECAMATAN|KECAMALAN|KECANZTAN|'
            r'AGAMA|STATUS|PERKAWINAN|PEKERJAAN|PEKENAAN|PEKORJAAN|KEWARGANEGARAAN|BERLAKU|HINGGA)\b',
            ' ',
            value,
            flags=re.I,
        )
        return self._clean_value(value)

    def _line_after(self, idx, max_offset=2):
        if idx < 0:
            return ''
        for j in range(idx + 1, min(len(self.lines), idx + max_offset + 1)):
            value = self._clean_value(self.lines[j])
            if value:
                return value
        return ''

    def _looks_like_name(self, value):
        upper = value.upper()
        if len(value) < 3:
            return False
        if re.search(r'\d{2}[-/\s.]\d{2}', value):
            return False
        blocked = ['PROVINSI', 'KABUPATEN', 'KOTA', 'NIK', 'TEMP', 'LAHIR', 'ALAMAT', 'RT', 'RW', 'KEL', 'KEC']
        return not any(token in upper for token in blocked) and bool(re.search(r'[A-Z]', upper))
    
    def _label_idx(self, kws):
        for i, l in enumerate(self.upper):
            for kw in kws:
                kwu = kw.upper()
                if kwu == 'KEL':
                    if re.search(r'\bKEL(?:\b|DESA\b|URAHAN\b)|\bKELURAHAN\b|\bKEL/DESA\b', l):
                        return i
                    continue
                if kwu == 'KEC':
                    if re.search(r'\bKEC(?:\b|AMATAN\b)|\bKECAMATAN\b', l):
                        return i
                    continue
                if re.search(rf'(^|[^A-Z0-9]){re.escape(kwu)}([^A-Z0-9]|$)', l):
                    return i
        return -1
    
    def _is_label(self, t):
        if not t: return True
        u = t.upper().strip()
        for l in ['NIK','NAMA','TEMPAT','LAHIR','TTL','TANGGAL','ALAMAT','RT','RW','KEL','DESA','KELURAHAN','KECAMATAN','KEC','KABUPATEN','KOTA','KAB','PROVINSI','AGAMA','STATUS','PERKAWINAN','PEKERJAAN','KEWARGANEGARAAN','BERLAKU','HINGGA','JENIS','KELAMIN','GOL','DARAH','PEKERJAAN','KAR','KARYAWAN','WNI','WNA','ISLAM','KRISTEN','KATOLIK','HINDU','BUDDHA','KONGHUCU']:
            if u == l or u.startswith(l + ' ') or u.startswith(l + ':'): return True
        return False
    
    def _near_label(self, kws, max_l=3):
        idx = self._label_idx(kws)
        if idx < 0: return '', 0.0
        for kw in kws:
            for ln in [self.lines[idx], self.upper[idx]]:
                for p in [rf'{kw}\s*[:.\-]?\s*(.+)', rf'{kw}\s+([A-Z0-9].+)']:
                    m = re.search(p, ln, re.IGNORECASE)
                    if m:
                        v = re.sub(r'\s+', ' ', m.group(1).strip())
                        if len(v) >= 2 and not self._is_label(v): return v.upper(), 0.9
        for i in range(idx+1, min(idx+max_l, len(self.lines))):
            ln = self.lines[i]
            if self._is_label(ln): continue
            u = ln.upper()
            stops = ['NIK','NAMA','JENIS','GOL','RT','RW','KEL','KEC','KAB','PROV','AGAMA','STATUS','PEKERJAAN','KEWARGAN','BERLAKU']
            if any(s in u for s in stops) and any(s == u or u.startswith(s+' ') for s in stops): continue
            if re.search(r'^\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4}$', ln): continue
            return ln.upper(), 0.8
        return '', 0.0
    
    def extract_nik(self):
        for m in re.findall(r'\b(\d{16})\b', self.raw): return m, 1.0
        idx = self._label_idx(['NIK'])
        if idx >= 0:
            for i in range(idx, min(idx+2, len(self.lines))):
                candidate_line = re.sub(r'\b(?:NIK|HIK)\b', ' ', self.lines[i], flags=re.I)
                digits = self._ocr_digits(candidate_line)
                if len(digits) == 16:
                    return digits, 0.95
                m = re.search(r'\d{16}', digits)
                if m and len(digits) <= 22:
                    return m.group(0), 0.9
                # OCR often returns 15 digits plus one bracket-like char for final "1".
                if len(digits) == 15 and re.search(r'[)\]Il|!]$', self.lines[i]):
                    return digits + '1', 0.82

        for line in self.lines:
            if re.search(r'NIK|HIK|\b20\s+\d', line, re.I):
                candidate_line = re.sub(r'\b(?:NIK|HIK)\b', ' ', line, flags=re.I)
                digits = self._ocr_digits(candidate_line)
                if len(digits) == 16:
                    return digits, 0.85
                m = re.search(r'\d{16}', digits)
                if m and len(digits) <= 22:
                    return m.group(0), 0.8
        return '', 0.0
    
    def extract_nama(self):
        idx = self._find_idx_regex([r'\bNAMA\b', r'\bNAMN\b', r'\bNAN\b', r'\bNARN\b', r'\bCAM\b'])
        if idx >= 0:
            ln = self.upper[idx]
            # Name BEFORE label: "JOHN Nama"
            m = re.search(r'([A-Z][A-Z\s]{2,})\s+(?:NAMA|NAMN|NAN|NARN|CAM)\b', ln, re.I)
            if m:
                v = m.group(1).strip()
                if len(v) >= 2: return v.upper(), 0.9
            # Name AFTER label: "Nama JOHN"
            for p in [r'(?:NAMA|NAMN|NAN|NARN|CAM)\s*[:.\-]?\s*(.+)', r'(?:NAMA|NAMN|NAN|NARN|CAM)\s+([A-Z][A-Z\s]+.+)']:
                m = re.search(p, ln, re.I)
                if m:
                    v = re.sub(r'\s+', ' ', m.group(1).strip())
                    if not any(x in v.upper() for x in ['TGL','TEMPAT','LAHIR']) and len(v) >= 2 and not self._is_label(v): return v.upper(), 0.9
            # Next line
            for i in range(idx+1, min(idx+2, len(self.lines))):
                nl = self.lines[i]
                nu = nl.upper()
                if any(x in nu for x in ['JL','JL.','KP','PERUM','DUSUN','RT','RW','NO ','NOMOR','NIK','JENIS','GOL']): continue
                if re.search(r'^\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4}', nl): continue
                if len(nl) >= 2 and not self._is_label(nl): return nl.upper(), 0.8

        nik_idx = self._label_idx(['NIK'])
        fallback = self._line_after(nik_idx, 1)
        fallback = re.sub(r'\b(NAMA|NAMN|NAN|NARN|CAM)\b', '', fallback, flags=re.I)
        fallback = self._remove_known_labels(fallback)
        if self._looks_like_name(fallback):
            return fallback.upper(), 0.72
        return '', 0.0
    
    def extract_tempat_lahir(self):
        idx = self._find_idx_regex([r'TEMP', r'TGL', r'LAHIR', r'LAHR', r'TSM'])
        if idx >= 0:
            ln = self.upper[idx]
            # City BEFORE TTL: "CITY TempauTgl Lahir"
            m = re.search(r'([A-Z][A-Z\s]+)\s+(?:TempauTgl|TEMPATTGL|Tgl\s+Lahir)', ln)
            if m:
                v = m.group(1).strip()
                if len(v) >= 2: return v.upper(), 1.0
            # CITY, DD-MM-YYYY
            m = re.search(r'([A-Z][A-Z\s.]+?)[,\s]+(\d{1,2})[-/.\s](\d{1,2})[-/.\s](\d{2,4})', ln)
            if m:
                v = m.group(1).strip()
                if len(v) >= 2: return v.upper(), 1.0
            # After TEMPAT label
            m = re.search(r'TEMPAT\s*[:.\-]?\s*(.+)', ln, re.I)
            if m:
                v = re.sub(r'\s+', ' ', m.group(1).strip())
                v = re.sub(r'\s*(?:TempauTgl|TEMPATTGL|TGL|LAHIR|TEMPAT).*', '', v, flags=re.I)
                v = re.sub(r'[,\s]+\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4}.*', '', v)
                if len(v) >= 2: return v.upper(), 0.9
            # Compact/partial TTL: "TepaTgLh SABBANG 19091997" or "SABBANG19-00 1997 ..."
            v = self._remove_known_labels(self.lines[idx])
            v = re.sub(r'^[^A-Za-z]+', '', v)
            v = re.sub(r'\d.*$', '', v).strip(' ,.-:/')
            if len(v) >= 2 and not any(x in v.upper() for x in ['KELAMIN', 'GOL', 'DARAH', 'ALAMAT']):
                return v.upper(), 0.72
            # Next lines (skip dates)
            for i in range(idx+1, min(idx+2, len(self.lines))):
                nl = self.lines[i]
                if re.search(r'^\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4}$', nl): continue
                if any(x in nl.upper() for x in ['TGL','LAHIR','TEMPAT','KELAMIN','GOL','DARAH','ALAMAT']): continue
                if len(nl) >= 2 and not self._is_label(nl): return nl.upper(), 0.8
        return '', 0.0
    
    def extract_tanggal_lahir(self):
        nik, _ = self.extract_nik()
        nik_date = self._birth_date_from_nik(nik)
        if nik_date:
            return nik_date, 0.92

        p = [r'(\d{1,2})[-/.\s](\d{1,2})[-/.\s](\d{4})', r'(\d{1,2})\s+(\d{1,2})\s+(\d{4})']
        idx = self._find_idx_regex([r'TEMP', r'TGL', r'LAHIR', r'LAHR', r'TSM'])
        if idx >= 0:
            for i in range(idx, min(idx+3, len(self.lines))):
                for pp in p:
                    m = re.search(pp, self.lines[i])
                    if m:
                        d,mo,y = m.groups()
                        if 1 <= int(mo) <= 12: return f"{d.zfill(2)}-{mo.zfill(2)}-{y}", 1.0
                digits = self._ocr_digits(self.lines[i])
                for start in range(0, max(len(digits) - 7, 0)):
                    part = digits[start:start+8]
                    if len(part) == 8:
                        d, mo, y = part[:2], part[2:4], part[4:]
                        if 1 <= int(d) <= 31 and 1 <= int(mo) <= 12 and 1900 <= int(y) <= 2100:
                            return f"{d}-{mo}-{y}", 0.82
                if len(digits) == 7:
                    d, mo, y = digits[:2], digits[2:4], '1' + digits[4:]
                    if 1 <= int(d) <= 31 and 1 <= int(mo) <= 12:
                        return f"{d}-{mo}-{y}", 0.72
        for ln in self.lines:
            if re.search(r'BERLAKU|HINGGA|SEUMUR|KEWARG', ln, re.I):
                continue
            for pp in p:
                m = re.search(pp, ln)
                if m:
                    d,mo,y = m.groups()
                    if 1 <= int(mo) <= 12: return f"{d.zfill(2)}-{mo.zfill(2)}-{y}", 1.0
        return '', 0.0
    
    def extract_jenis_kelamin(self):
        for ln in self.upper:
            if 'LAKI' in ln: return 'LAKI-LAKI', 1.0
            if 'PEREMPUAN' in ln: return 'PEREMPUAN', 1.0
        return '', 0.0
    
    def extract_gol_darah(self):
        idx = self._label_idx(['GOL','DARAH'])
        if idx >= 0:
            for i in range(idx, min(idx+2, len(self.lines))):
                m = re.search(r'\b(A|B|AB|O)[+-]?\b', self.lines[i], re.I)
                if m: return m.group(1).upper(), 0.9
        for ln in self.lines:
            m = re.search(r'\b(A|B|AB|O)[+-]?\b', ln, re.I)
            if m: return m.group(1).upper(), 0.7
        return '', 0.0
    
    def extract_alamat(self):
        idx = self._find_idx_regex([r'ALAMAT', r'ALAMAL', r'MLAMAR', r'NAMAT', r'AAMAL', r'\bAAM\b'])
        if idx >= 0:
            direct = self._value_around_regex(self.lines[idx], [r'ALAMAT', r'ALAMAL', r'MLAMAR', r'NAMAT', r'AAMAL', r'\bAAM\b'])
            if len(direct) >= 3 and not self._is_label(direct):
                return direct.upper(), 0.9
            parts = []
            for i in range(idx, min(idx+5, len(self.lines))):
                ln = self.lines[i]
                u = ln.upper()
                if 'RT' in u and 'RW' in u: break
                if re.search(r'\d{1,3}\s*[/:]\s*\d{1,3}', ln): break
                if any(x in u for x in ['KEL','DESA','KELURAHAN','KEC','KECAMATAN','NIK']): break
                if i == idx:
                    m = re.search(r'ALAMAT\s*[:.\-]?\s*(.+)', u, re.I)
                    if m and len(m.group(1).strip()) >= 2: parts.append(m.group(1).strip())
                elif len(ln) >= 2 and not self._is_label(ln): parts.append(ln)
            if parts: return ' '.join(parts).upper(), 0.9
        for ln in self.lines:
            for p in [r'JL\.?\s+(.+)',r'JALAN\s+(.+)',r'KP\.?\s+(.+)',r'PERUM\s+(.+)',r'DUSUN\s+(.+)']:
                m = re.search(p, ln, re.I)
                if m and len(m.group(1).strip()) >= 3: return m.group(1).strip().upper(), 0.8
        return '', 0.0
    
    def extract_rt_rw(self):
        for ln in self.lines:
            m = re.search(r'RT\s*(\d{1,3})\s*[/:]\s*RW\s*(\d{1,3})', ln, re.I)
            if m and self._valid_rt_rw(m.group(1), m.group(2)):
                return f"RT {m.group(1).zfill(3)}/RW {m.group(2).zfill(3)}", 1.0
            if re.search(r'RT|RW|ATRW|ATIRW|RTRW|RTIRW|RIRW', ln, re.I):
                stripped = re.sub(r'RT\s*/?\s*RW|ATIRW|ATRW|RTRW|RTIRW|RIRW|RT|RW', ' ', ln, flags=re.I)
                digits = re.findall(r'\d{1,3}', self._ocr_digits(stripped))
                if len(digits) >= 2 and self._valid_rt_rw(digits[0], digits[1]):
                    return f"RT {digits[0].zfill(3)}/RW {digits[1].zfill(3)}", 0.85
            m = re.search(r'(?<![RT\s])(\d{1,3})\s*[/:]\s*(\d{1,3})(?!\s*/\s*\d)', ln)
            if m and self._valid_rt_rw(m.group(1), m.group(2)):
                return f"RT {m.group(1).zfill(3)}/RW {m.group(2).zfill(3)}", 0.9
        return '', 0.0

    def _valid_rt_rw(self, rt, rw):
        try:
            rt_i = int(rt)
            rw_i = int(rw)
        except ValueError:
            return False
        return 0 <= rt_i <= 80 and 0 <= rw_i <= 80
    
    def extract_kel_desa(self):
        idx = self._find_idx_regex([r'KEL\s*/?\s*DESA', r'KELDESA', r'KEUDESA', r'KEDESE', r'KET/DER', r'KELL?DESA'])
        if idx >= 0:
            v = self._value_around_regex(self.lines[idx], [r'KEL\s*/?\s*DESA', r'KELDESA', r'KEUDESA', r'KEDESE', r'KET/DER', r'KELL?DESA'])
            if len(v) >= 2:
                return v.upper(), 0.9
        return self._near_label(['KEL','DESA','KELURAHAN'])

    def extract_kecamatan(self):
        idx = self._find_idx_regex([r'KECAMATAN', r'KECAMALAN', r'KECANZTAN', r'KECAM\w+', r'KCAMLAN'])
        if idx >= 0:
            v = self._value_around_regex(self.lines[idx], [r'KECAMATAN', r'KECAMALAN', r'KECANZTAN', r'KECAM\w+', r'KCAMLAN'])
            if len(v) >= 2:
                return v.upper(), 0.9
        return self._near_label(['KECAMATAN','KEC'])

    def extract_provinsi(self):
        idx = self._find_idx_regex([r'PROVINSI', r'PHOVINSI', r'PAOVINSI', r'PROVISI'])
        if idx >= 0:
            v = self._value_around_regex(self.lines[idx], [r'PROVINSI', r'PHOVINSI', r'PAOVINSI', r'PROVISI'], prefer='after')
            if len(v) >= 2:
                return v.upper(), 0.9
        return self._near_label(['PROVINSI'])
    
    def extract_kab_kota(self):
        idx = self._find_idx_regex([r'KABUPATEN', r'KABUPATEN\w+', r'KAHUPATEN', r'KOTA'])
        if idx >= 0:
            ln = self.upper[idx]
            for p in [r'KABUPATEN\s*[:.\-]?\s*(.+)',r'KOTA\s*[:.\-]?\s*(.+)',r'KABUPATEN/KOTA\s*[:.\-]?\s*(.+)', r'(.+)\s+KABUPATEN\w*']:
                m = re.search(p, ln, re.I)
                if m and len(m.group(1).strip()) >= 2: return m.group(1).strip().upper(), 0.9
            for i in range(idx+1, min(idx+2, len(self.lines))):
                nl = self.lines[i]
                if len(nl) >= 2 and not self._is_label(nl): return nl.upper(), 0.8
        return '', 0.0
    
    def extract_agama(self):
        for ln in self.upper:
            for p,n in [('ISLAM','Islam'),('KRISTEN','Kristen'),('KATOLIK','Katolik'),('HINDU','Hindu'),('BUDDHA','Buddha'),('KONGHUCU','Konghucu')]:
                if p in ln: return n, 1.0
        return '', 0.0
    
    def extract_status_kawin(self):
        for ln in self.upper:
            normalized = re.sub(r'\s+', '', ln)
            if 'BELUMKAWIN' in normalized:
                return 'Belum Kawin', 1.0
            for p,n in [('BELUM KAWIN','Belum Kawin'),('KAWIN','Kawin'),('CERAI HIDUP','Cerai Hidup'),('CERAI MATI','Cerai Mati')]:
                if p in ln: return n, 1.0
        return '', 0.0
    
    def extract_pekerjaan(self):
        idx = self._find_idx_regex([r'PEKERJAAN', r'PEKENAAN', r'PEKORJAAN', r'PEK\w+AN'])
        if idx >= 0:
            v = self._value_around_regex(self.lines[idx], [r'PEKERJAAN', r'PEKENAAN', r'PEKORJAAN', r'PEK\w+AN'])
            v = re.sub(r'\b(WNI|WNA|KEWARGANEGARAAN|BERLAKU|HINGGA|SEUMUR|HIDUP)\b.*$', '', v, flags=re.I)
            v = re.sub(r'\b\d{1,2}[-/.]\d{1,2}[-/.]\d{2,4}\b.*$', '', v)
            v = re.sub(r'\d.*$', '', v).strip()
            if len(v) >= 2 and not any(x in v.upper() for x in ['STATUS', 'SATUS', 'STALUS', 'PERKAWINAN', 'PERKEWINAN']):
                return v.upper(), 0.85
        return self._near_label(['PEKERJAAN'])
    
    def extract_kewarganegaraan(self):
        for ln in self.upper:
            if 'WNI' in ln: return 'WNI', 1.0
            if 'WNA' in ln: return 'WNA', 1.0
        return 'WNI', 0.5
    
    def extract_berlaku(self):
        for ln in self.upper:
            if 'BERLAKU' in ln:
                if 'SEUMUR' in ln or 'SEPANJANG' in ln: return 'SEUMUR HIDUP', 1.0
                m = re.search(r'(\d{1,2})[-/.](\d{1,2})[-/.](\d{4})', ln)
                if m: return f"{m.group(1).zfill(2)}-{m.group(2).zfill(2)}-{m.group(3)}", 1.0
        return '', 0.0
    
    def parse(self):
        field_map = {
            'nik': 'nik', 'nama_lengkap': 'nama', 'tempat_lahir': 'tempat_lahir',
            'tanggal_lahir': 'tanggal_lahir', 'jenis_kelamin': 'jenis_kelamin',
            'gol_darah': 'gol_darah', 'alamat': 'alamat', 'rt_rw': 'rt_rw',
            'kel_desa': 'kel_desa', 'kec': 'kecamatan', 'kab_kota': 'kab_kota',
            'provinsi': 'provinsi', 'agama': 'agama', 'status_perkawinan': 'status_kawin',
            'pekerjaan': 'pekerjaan', 'kewarganegaraan': 'kewarganegaraan',
            'berlaku_hingga': 'berlaku'
        }
        data = {}
        conf = {}
        for f, m in field_map.items():
            val, c = getattr(self, 'extract_'+m)()
            data[f] = val
            conf[f] = c
        valid = [c for c in conf.values() if c > 0]
        avg = sum(valid)/len(valid) if valid else 0.0
        return {**data, '_raw_text': self.raw, '_confidence_avg': round(avg,4), '_field_confidence': conf}


def sort_ocr_results(results):
    return sorted(results, key=lambda x: sum(p[1] for p in x[0])/len(x[0]))


def raw_from_ocr_results(results):
    sorted_r = sort_ocr_results(results)
    lines, cur, last_y = [], [], None
    for (bbox, text, conf) in sorted_r:
        text = text.strip()
        if not text:
            continue
        avg_y = sum(p[1] for p in bbox)/len(bbox)
        if last_y and abs(avg_y-last_y) > 20:
            if cur:
                lines.append(' '.join(cur))
            cur = []
        cur.append(text)
        last_y = avg_y
    if cur:
        lines.append(' '.join(cur))
    return '\n'.join(lines)


def parse_raw_text(raw, source, meta=None):
    parser = KtpOcrParser(raw)
    data = parser.parse()
    data = normalize_parsed_data(data)
    data['_ocr_source'] = source
    if meta:
        data['_ocr_meta'] = meta
    return data


def normalize_parsed_data(data):
    replacements = {
        'BARAL': 'BARAT',
        'BAHAI': 'BARAT',
        'JAKAAIA': 'JAKARTA',
        'PANDEGLNG': 'PANDEGLANG',
        'PANDEGLING': 'PANDEGLANG',
        'PANDEGLG': 'PANDEGLANG',
        'CIBEUNYINA': 'CIBEUNYING',
        'ISUAM': 'ISLAM',
    }
    for key in ['provinsi', 'kab_kota', 'kec', 'kel_desa', 'agama', 'alamat', 'tempat_lahir', 'pekerjaan']:
        value = data.get(key)
        if not value:
            continue
        upper = str(value).upper()
        for old, new in replacements.items():
            upper = upper.replace(old, new)
        data[key] = upper
    pekerjaan = str(data.get('pekerjaan') or '').upper()
    if any(token in pekerjaan for token in ['STATUS', 'SATUS', 'STALUS', 'PERKAWINAN', 'PERKEWINAN']):
        data['pekerjaan'] = ''
    return data


def has_core_ocr_data(data):
    if not data:
        return False
    return bool(
        data.get('nik')
        or data.get('nama_lengkap')
        or data.get('alamat')
        or float(data.get('_confidence_avg') or 0) >= 0.25
    )


def is_valid_nik(value):
    digits = re.sub(r'\D+', '', value or '')
    if len(digits) != 16:
        return False
    try:
        day = int(digits[6:8])
        month = int(digits[8:10])
    except ValueError:
        return False
    valid_day = 1 <= day <= 31 or 41 <= day <= 71
    return valid_day and 1 <= month <= 12


def merge_support_fields(primary, support):
    if not primary or not support:
        return primary

    primary_conf = primary.get('_field_confidence') or {}
    support_conf = support.get('_field_confidence') or {}
    filled = []
    replaced = []
    for key, value in support.items():
        if key.startswith('_'):
            continue
        current = primary.get(key)
        if value and (not current):
            primary[key] = value
            primary_conf[key] = min(float(support_conf.get(key, 0.0) or 0.0), 0.75)
            filled.append(key)
        elif value and should_replace_with_support(key, str(current), str(value)):
            primary[key] = value
            primary_conf[key] = min(float(support_conf.get(key, 0.0) or 0.0), 0.75)
            replaced.append(key)

    primary['_field_confidence'] = primary_conf
    if filled or replaced:
        primary['_ocr_support_filled_fields'] = filled
        primary['_ocr_support_replaced_fields'] = replaced
        valid = [float(c) for c in primary_conf.values() if float(c or 0) > 0]
        primary['_confidence_avg'] = round(sum(valid) / len(valid), 4) if valid else 0.0
    return primary


def should_replace_with_support(key, current, support):
    if not current or not support:
        return False

    current = current.strip()
    support = support.strip()
    if current == support:
        return False

    if key == 'nik':
        return is_valid_nik(support) and not is_valid_nik(current)

    if key == 'tanggal_lahir':
        if bool(re.fullmatch(r'\d{2}-\d{2}-\d{4}', support)) and not re.fullmatch(r'\d{2}-\d{2}-\d{4}', current):
            return True
        if re.search(r'201[0-9]|202[0-9]', current) and re.search(r'19[0-9]{2}|200[0-9]', support):
            return True

    if key == 'rt_rw':
        current_nums = [int(n) for n in re.findall(r'\d{1,3}', current)]
        support_nums = [int(n) for n in re.findall(r'\d{1,3}', support)]
        if len(current_nums) >= 2 and len(support_nums) >= 2:
            current_suspicious = current_nums[0] > 80 or current_nums[1] > 80
            support_plausible = support_nums[0] <= 80 and support_nums[1] <= 80
            if current_suspicious and support_plausible:
                return True
            if current_nums[0] >= 50 and 0 < support_nums[0] < current_nums[0] and support_plausible:
                return True

    if key == 'status_perkawinan':
        return current.lower() == 'kawin' and 'belum' in support.lower()

    if key in {'nama_lengkap', 'tempat_lahir', 'alamat', 'kel_desa', 'kec', 'kab_kota', 'provinsi', 'pekerjaan'}:
        has_letters = bool(re.search(r'[A-Za-z]', current))
        zero_ratio = current.count('0') / max(len(current), 1)
        suspicious_zero_blank = has_letters and (
            zero_ratio >= 0.20 or bool(re.search(r'0[A-Za-z]0|[A-Za-z]0[A-Za-z]', current))
        )
        too_many_digits = sum(ch.isdigit() for ch in current) > max(1, len(current) // 4)
        support_has_letters = bool(re.search(r'[A-Za-z]', support))
        if support_has_letters and (suspicious_zero_blank or too_many_digits):
            return True

        if key == 'nama_lengkap':
            current_has_digits = bool(re.search(r'\d', current))
            support_has_digits = bool(re.search(r'\d', support))
            if current_has_digits and not support_has_digits:
                return True
            if len(current) <= 4 and len(support) >= 7 and not support_has_digits:
                return True

        if key == 'alamat':
            if re.fullmatch(r'[\d\s/.-]+', current) and re.search(r'[A-Za-z]{3,}', support):
                return True

        current_norm = re.sub(r'[^A-Z0-9]+', '', current.upper())
        support_norm = re.sub(r'[^A-Z0-9]+', '', support.upper())
        if key == 'tempat_lahir' and re.search(r'TEMP|TGL|LAH|TEEAU|TEPA', current.upper()):
            return bool(support_norm)
        cross_field_noise = any(token in current.upper() for token in [
            'GOL', 'DARAH', 'KELAMIN', 'AGAMA', 'PEKERJAAN', 'KEWARGANEGARAAN', 'BERLAKU'
        ])
        if support_has_letters and cross_field_noise and key in {'kel_desa', 'kec', 'kab_kota', 'provinsi', 'alamat', 'pekerjaan'}:
            return True

        if support_has_letters and current_norm and support_norm:
            distance_ratio = levenshtein_distance(current_norm, support_norm) / max(len(support_norm), 1)
            length_gap = abs(len(current_norm) - len(support_norm)) / max(len(support_norm), 1)
            if distance_ratio <= 0.28 and length_gap <= 0.35 and current_norm != support_norm:
                return True

    return False


def levenshtein_distance(a, b):
    if a == b:
        return 0
    if not a:
        return len(b)
    if not b:
        return len(a)

    prev = list(range(len(b) + 1))
    for i, ca in enumerate(a, 1):
        cur = [i]
        for j, cb in enumerate(b, 1):
            cost = 0 if ca == cb else 1
            cur.append(min(cur[-1] + 1, prev[j] + 1, prev[j - 1] + cost))
        prev = cur
    return prev[-1]


def run_easyocr_detection(path):
    if not EASYOCR_AVAILABLE:
        return []
    reader = get_reader()
    if not reader:
        return []
    return reader.readtext(path, detail=1)


def score_ocr_data(data):
    if not data:
        return -1.0

    score = 0.0
    if is_valid_nik(data.get('nik', '')):
        score += 5.0
    elif data.get('nik'):
        score += 1.0

    weighted_fields = {
        'nama_lengkap': 1.5,
        'tanggal_lahir': 1.2,
        'jenis_kelamin': 1.2,
        'alamat': 1.0,
        'rt_rw': 0.8,
        'kel_desa': 0.8,
        'kec': 0.8,
        'kab_kota': 0.8,
        'provinsi': 0.8,
        'agama': 0.5,
        'status_perkawinan': 0.5,
        'pekerjaan': 0.4,
    }
    for field, weight in weighted_fields.items():
        if data.get(field):
            score += weight

    score += float(data.get('_confidence_avg') or 0.0)
    raw = data.get('_raw_text') or ''
    if re.search(r'PROVINSI|KABUPATEN|NIK|NAMA|ALAMAT', raw, re.I):
        score += 1.0
    return score


def should_try_rotation_fallback(data):
    if not data:
        return True
    if is_valid_nik(data.get('nik', '')) and data.get('nama_lengkap'):
        return False
    filled = sum(1 for k, v in data.items() if not k.startswith('_') and v)
    return filled < 6 or score_ocr_data(data) < 6.0


def rotated_candidates(path):
    if not IMAGE_LIBS_AVAILABLE:
        return []
    img = cv2.imread(path)
    if img is None:
        return []

    h, w = img.shape[:2]
    base, _ = os.path.splitext(path)
    angles = [180]
    if h > w:
        angles = portrait_rotation_order(img)
    candidates = []
    for angle in angles:
        if angle == 90:
            rotated = cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)
        elif angle == 180:
            rotated = cv2.rotate(img, cv2.ROTATE_180)
        elif angle == 270:
            rotated = cv2.rotate(img, cv2.ROTATE_90_COUNTERCLOCKWISE)
        else:
            continue
        out = f"{base}_autorot{angle}.png"
        cv2.imwrite(out, rotated)
        candidates.append((angle, out))
    return candidates


def portrait_rotation_order(img):
    scored = []
    for angle, rotated in [
        (90, cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)),
        (270, cv2.rotate(img, cv2.ROTATE_90_COUNTERCLOCKWISE)),
    ]:
        h, w = rotated.shape[:2]
        left = rotated[:, :w // 2]
        right = rotated[:, w // 2:]
        scored.append((photo_side_score(right) - photo_side_score(left), angle))
    return [angle for _, angle in sorted(scored, reverse=True)]


def photo_side_score(region):
    try:
        hsv = cv2.cvtColor(region, cv2.COLOR_BGR2HSV)
        gray = cv2.cvtColor(region, cv2.COLOR_BGR2GRAY)
        return float(hsv[:, :, 1].mean()) + float(gray.std())
    except Exception:
        return 0.0


def is_portrait_image(path):
    if not IMAGE_LIBS_AVAILABLE:
        return False
    img = cv2.imread(path)
    if img is None:
        return False
    h, w = img.shape[:2]
    return h > w


def _process_ocr_single(path):
    if not CRNN_AVAILABLE and not EASYOCR_AVAILABLE:
        return {'success':False,'message':'CRNN and EasyOCR are not available','data':None}

    proc = path
    try:
        proc = preprocess_image(path)
        easy_results = []
        easy_data = None

        # EasyOCR is used first as a detector/support signal. Recognition is
        # attempted with the trained CRNN models for every detected crop.
        if EASYOCR_AVAILABLE:
            try:
                easy_results = run_easyocr_detection(proc)
                if easy_results:
                    easy_raw = raw_from_ocr_results(easy_results)
                    easy_data = parse_raw_text(easy_raw, 'easyocr_support')
            except Exception as e:
                print(f"# Warning: EasyOCR support failed: {e}", file=sys.stderr)
                easy_results = []

        crnn_data = None
        if CRNN_AVAILABLE and recognize_image_with_crnn:
            try:
                crnn_result = recognize_image_with_crnn(proc, detections=easy_results)
                if crnn_result.get('success'):
                    crnn_meta = crnn_result.get('stats') or {}
                    crnn_meta['crnn_confidence'] = crnn_result.get('confidence', 0)
                    crnn_data = parse_raw_text(
                        crnn_result.get('raw_text') or '',
                        'crnn_primary',
                        crnn_meta,
                    )
            except Exception as e:
                print(f"# Warning: CRNN primary OCR failed: {e}", file=sys.stderr)

        selected = None
        if has_core_ocr_data(crnn_data):
            selected = merge_support_fields(crnn_data, easy_data)
        elif easy_data:
            selected = easy_data
            selected['_ocr_source'] = 'easyocr_fallback_after_crnn'
            if crnn_data:
                selected['_ocr_meta'] = {
                    'crnn_raw_text': crnn_data.get('_raw_text', ''),
                    'crnn_confidence_avg': crnn_data.get('_confidence_avg', 0),
                    'fallback_reason': 'crnn_no_core_ktp_fields',
                }
        elif crnn_data:
            selected = crnn_data

        if proc != path and os.path.exists(proc):
            os.remove(proc)

        if selected:
            return {'success':True,'message':'OCR berhasil','data':selected}

        return {'success':False,'message':'Tidak ada teks yang berhasil diekstrak','data':None}
    except Exception as e:
        import traceback
        print(f"Error: {e}\n{traceback.format_exc()}", file=sys.stderr)
        return {'success':False,'message':f'Error: {e}','data':None}
    finally:
        if proc != path and os.path.exists(proc):
            try:
                os.remove(proc)
            except:
                pass


def process_ocr(path):
    portrait = is_portrait_image(path)
    if portrait:
        best = {'success': False, 'message': 'Portrait image requires autorotate', 'data': None}
        best_data = None
    else:
        best = _process_ocr_single(path)
        best_data = best.get('data') if isinstance(best, dict) else None

        if best.get('success') and not should_try_rotation_fallback(best_data):
            return best

    candidates = []
    try:
        candidates = rotated_candidates(path)
        best_score = score_ocr_data(best_data)
        best_angle = 0
        for angle, candidate_path in candidates:
            result = _process_ocr_single(candidate_path)
            data = result.get('data') if isinstance(result, dict) else None
            score = score_ocr_data(data)
            if result.get('success') and score > best_score:
                best = result
                best_score = score
                best_angle = angle
                if not should_try_rotation_fallback(data):
                    break

        if best.get('success') and best.get('data') is not None:
            meta = best['data'].get('_ocr_meta') or {}
            meta['autorotate_angle'] = best_angle
            meta['autorotate_score'] = round(best_score, 4)
            best['data']['_ocr_meta'] = meta
        return best
    finally:
        for _, candidate_path in candidates:
            if os.path.exists(candidate_path):
                try:
                    os.remove(candidate_path)
                except:
                    pass


# Flask Routes
@app.route("/health")
def health():
    crnn = diagnose_crnn() if CRNN_AVAILABLE and diagnose_crnn else {"available": False}
    return jsonify({
        "status": "ok",
        "service": "KTP OCR",
        "version": "4.0",
        "primary": "ktp_crnn_v2",
        "crnn": crnn,
        "easyocr_support": EASYOCR_AVAILABLE,
        "image_libs": IMAGE_LIBS_AVAILABLE,
    })

@app.route("/api/ocr/ktp", methods=["POST"])
def ocr_ktp():
    try:
        path = None
        if "image" in request.files:
            f = request.files["image"]
            if not f.filename: return jsonify({"success":False,"message":"No file"}),400
            if not allowed_file(f.filename): return jsonify({"success":False,"message":"Invalid format"}),400
            path = os.path.join(app.config['UPLOAD_FOLDER'], secure_filename(f.filename))
            f.save(path)
        elif request.is_json and "image_base64" in request.json:
            b64 = request.json["image_base64"]
            if "," in b64: b64 = b64.split(",")[1]
            img = Image.open(io.BytesIO(base64.b64decode(b64)))
            path = os.path.join(app.config['UPLOAD_FOLDER'], "temp.jpg")
            img.save(path)
        else:
            return jsonify({"success":False,"message":"Send as 'image' or JSON with 'image_base64'"}),400
        result = process_ocr(path)
        if path and os.path.exists(path):
            try: os.remove(path)
            except: pass
        return jsonify(result) if result['success'] else jsonify(result),500
    except Exception as e:
        return jsonify({"success":False,"message":str(e)}),500

@app.route("/api/ocr/batch", methods=["POST"])
def ocr_batch():
    key = "images[]" if "images[]" in request.files else "images"
    if key not in request.files: return jsonify({"success":False,"message":"No files"}),400
    results = []
    for f in request.files.getlist(key):
        if f and allowed_file(f.filename):
            path = os.path.join(app.config['UPLOAD_FOLDER'], secure_filename(f.filename))
            f.save(path)
            r = process_ocr(path)
            r['filename'] = f.filename
            results.append(r)
            if os.path.exists(path):
                try: os.remove(path)
                except: pass
    return jsonify({"success":True,"total":len(results),"results":results})

if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] in ["--diagnose", "--health"]:
        crnn = diagnose_crnn() if CRNN_AVAILABLE and diagnose_crnn else {"available": False}
        print(json.dumps({
            "status": "ok",
            "service": "KTP OCR",
            "version": "4.0",
            "primary": "ktp_crnn_v2",
            "crnn": crnn,
            "easyocr_support": EASYOCR_AVAILABLE,
            "image_libs": IMAGE_LIBS_AVAILABLE,
        }, ensure_ascii=False, indent=2))
        sys.exit(0)

    if len(sys.argv) > 1 and sys.argv[1] not in ["-h","--help"]:
        path = sys.argv[1]
        if not os.path.exists(path):
            print(json.dumps({'status':'error','error':f'Not found: {path}'}), file=sys.stderr)
            sys.exit(1)
        if not CRNN_AVAILABLE and not EASYOCR_AVAILABLE:
            print(json.dumps({'status':'error','error':'CRNN and EasyOCR are not available'}), file=sys.stderr)
            sys.exit(1)
        import contextlib
        with contextlib.redirect_stderr(open(os.devnull, 'w')):
            result = process_ocr(path)
        if result['success']:
            out = {
                'status':'success',
                'raw':result['data']['_raw_text'],
                'raw_text':result['data']['_raw_text'],
                'data':{k:v for k,v in result['data'].items() if not k.startswith('_')},
                'conf':result['data']['_confidence_avg'],
                'confidence':result['data']['_confidence_avg'],
                'field_confidence': result['data'].get('_field_confidence', {}),
                'ocr_source': result['data'].get('_ocr_source', 'unknown'),
                'ocr_meta': result['data'].get('_ocr_meta', {}),
            }
            print(json.dumps(out, ensure_ascii=False))
        else:
            print(json.dumps({'status':'error','error':result['message']}), file=sys.stderr)
        sys.exit(0)
    port = int(os.environ.get('PORT', 5000))
    print(f"KTP OCR Service di http://localhost:{port} | CRNN: {CRNN_AVAILABLE} | EasyOCR support: {EASYOCR_AVAILABLE}")
    app.run(host="0.0.0.0", port=port, debug=False)
