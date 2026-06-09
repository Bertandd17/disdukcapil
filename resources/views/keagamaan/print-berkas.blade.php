<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Pernikahan - {{ $pernikahan->nomor_antrian }}</title>
    <style>
        * {
            font-family: 'Times New Roman', serif;
        }
        body {
            padding: 20px;
            font-size: 12pt;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0 0 0;
        }
        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 30px 0;
            text-decoration: underline;
        }
        .info-box {
            border: 1px solid #000;
            padding: 15px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            margin: 8px 0;
        }
        .info-label {
            width: 200px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        .section {
            margin: 25px 0;
        }
        .section-title {
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 10px;
            background: #f0f0f0;
            padding: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table td, table th {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        .dokumen-list {
            list-style: none;
            padding: 0;
        }
        .dokumen-list li {
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .dokumen-list li:before {
            content: "✓";
            margin-right: 10px;
            color: green;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10pt;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            width: 200px;
            text-align: center;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
    {{-- SweetAlert Final Fix --}}
    <link rel="stylesheet" href="{{ asset('css/swal-final-fix.css') }}">
</head>
<body>
    {{-- Print Button (no print) --}}
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-print"></i> Cetak
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #e5e7eb; color: #1f2937; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    {{-- Header --}}
    <div class="header">
        <img src="{{ public_path('images/logo_toba.jpeg') }}" alt="Logo Kabupaten Toba" onerror="this.style.display='none'">
        <h1>Pemerintah Kabupaten Toba</h1>
        <h2>Dinas Kependudukan dan Pencatatan Sipil</h2>
        <p style="margin: 10px 0 0 0; font-weight: bold;">Jalan Pardede Onan, Balige, Kabupaten Toba</p>
    </div>

    {{-- Title --}}
    <div class="title">
        BERKAS PERMINTAAN PENCATATAN PERKAWINAN<br>
        NOMOR: {{ $pernikahan->nomor_antrian }}
    </div>

    {{-- Info Box --}}
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nomor Antrian:</span>
            <span class="info-value"><strong>{{ $pernikahan->nomor_antrian }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Permohonan:</span>
            <span class="info-value">{{ $pernikahan->created_at->format('d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value"><strong>{{ $pernikahan->status_label }}</strong></span>
        </div>
    </div>

    {{-- Data Mempelai --}}
    <div class="section">
        <div class="section-title">DATA MEMPELAI</div>
        <table>
            <tr>
                <td colspan="2" style="background: #f9f9f9; font-weight: bold;">MEMPELAI PRIA</td>
            </tr>
            <tr>
                <td style="width: 200px;">Nama Lengkap</td>
                <td>: {{ $pernikahan->nama_mempelai_pria }}</td>
            </tr>
            <tr>
                <td>NIK</td>
                <td>: {{ $pernikahan->nik_mempelai_pria }}</td>
            </tr>
            @if($pernikahan->tempat_lahir_mempelai_pria)
            <tr>
                <td>Tempat/Tanggal Lahir</td>
                <td>: {{ $pernikahan->tempat_lahir_mempelai_pria }}, {{ $pernikahan->tanggal_lahir_mempelai_pria?->format('d F Y') }}</td>
            </tr>
            @endif
            @if($pernikahan->agama_mempelai_pria)
            <tr>
                <td>Agama</td>
                <td>: {{ $pernikahan->agama_mempelai_pria }}</td>
            </tr>
            @endif
            @if($pernikahan->pekerjaan_mempelai_pria)
            <tr>
                <td>Pekerjaan</td>
                <td>: {{ $pernikahan->pekerjaan_mempelai_pria }}</td>
            </tr>
            @endif
            @if($pernikahan->alamat_mempelai_pria)
            <tr>
                <td>Alamat</td>
                <td>: {{ $pernikahan->alamat_mempelai_pria }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" style="background: #f9f9f9; font-weight: bold;">MEMPELAI WANITA</td>
            </tr>
            <tr>
                <td>Nama Lengkap</td>
                <td>: {{ $pernikahan->nama_mempelai_wanita }}</td>
            </tr>
            <tr>
                <td>NIK</td>
                <td>: {{ $pernikahan->nik_mempelai_wanita }}</td>
            </tr>
            @if($pernikahan->tempat_lahir_mempelai_wanita)
            <tr>
                <td>Tempat/Tanggal Lahir</td>
                <td>: {{ $pernikahan->tempat_lahir_mempelai_wanita }}, {{ $pernikahan->tanggal_lahir_mempelai_wanita?->format('d F Y') }}</td>
            </tr>
            @endif
            @if($pernikahan->agama_mempelai_wanita)
            <tr>
                <td>Agama</td>
                <td>: {{ $pernikahan->agama_mempelai_wanita }}</td>
            </tr>
            @endif
            @if($pernikahan->pekerjaan_mempelai_wanita)
            <tr>
                <td>Pekerjaan</td>
                <td>: {{ $pernikahan->pekerjaan_mempelai_wanita }}</td>
            </tr>
            @endif
            @if($pernikahan->alamat_mempelai_wanita)
            <tr>
                <td>Alamat</td>
                <td>: {{ $pernikahan->alamat_mempelai_wanita }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Data Orang Tua --}}
    <div class="section">
        <div class="section-title">DATA ORANG TUA MEMPELAI PRIA</div>
        <table>
            <tr>
                <td colspan="2" style="background: #f9f9f9; font-weight: bold;">AYAH</td>
            </tr>
            @if($pernikahan->nama_ayah_pria)
            <tr>
                <td style="width: 200px;">Nama</td>
                <td>: {{ $pernikahan->nama_ayah_pria }}</td>
            </tr>
            @endif
            @if($pernikahan->nik_ayah_pria)
            <tr>
                <td>NIK</td>
                <td>: {{ $pernikahan->nik_ayah_pria }}</td>
            </tr>
            @endif
            @if($pernikahan->nama_ibu_pria)
            <tr>
                <td colspan="2" style="background: #f9f9f9; font-weight: bold;">IBU</td>
            </tr>
            <tr>
                <td>Nama</td>
                <td>: {{ $pernikahan->nama_ibu_pria }}</td>
            </tr>
            @endif
            @if($pernikahan->nik_ibu_pria)
            <tr>
                <td>NIK</td>
                <td>: {{ $pernikahan->nik_ibu_pria }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Data Saksi --}}
    <div class="section">
        <div class="section-title">DATA SAKSI</div>
        <table>
            <tr>
                <td colspan="2" style="background: #f9f9f9; font-weight: bold;">SAKSI 1</td>
            </tr>
            @if($pernikahan->nama_saksi_1)
            <tr>
                <td style="width: 200px;">Nama</td>
                <td>: {{ $pernikahan->nama_saksi_1 }}</td>
            </tr>
            @endif
            @if($pernikahan->nik_saksi_1)
            <tr>
                <td>NIK</td>
                <td>: {{ $pernikahan->nik_saksi_1 }}</td>
            </tr>
            @endif
            @if($pernikahan->alamat_saksi_1)
            <tr>
                <td>Alamat</td>
                <td>: {{ $pernikahan->alamat_saksi_1 }}</td>
            </tr>
            @endif
            @if($pernikahan->nama_saksi_2)
            <tr>
                <td colspan="2" style="background: #f9f9f9; font-weight: bold;">SAKSI 2</td>
            </tr>
            <tr>
                <td>Nama</td>
                <td>: {{ $pernikahan->nama_saksi_2 }}</td>
            </tr>
            @endif
            @if($pernikahan->nik_saksi_2)
            <tr>
                <td>NIK</td>
                <td>: {{ $pernikahan->nik_saksi_2 }}</td>
            </tr>
            @endif
            @if($pernikahan->alamat_saksi_2)
            <tr>
                <td>Alamat</td>
                <td>: {{ $pernikahan->alamat_saksi_2 }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Waktu & Tempat --}}
    <div class="section">
        <div class="section-title">WAKTU & TEMPAT PERKAWINAN</div>
        <table>
            @if($pernikahan->tanggal_perkawinan)
            <tr>
                <td style="width: 200px;">Tanggal Perkawinan</td>
                <td>: {{ $pernikahan->tanggal_perkawinan->format('d F Y') }}</td>
            </tr>
            @endif
            @if($pernikahan->nama_gereja)
            <tr>
                <td>Gereja/Tempat Ibadah</td>
                <td>: {{ $pernikahan->nama_gereja }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Daftar Dokumen --}}
    @if($pernikahan->dokumen->isNotEmpty())
    <div class="section">
        <div class="section-title">DAFTAR DOKUMEN YANG DIUPLOAD</div>
        <ul class="dokumen-list">
            @foreach($pernikahan->dokumen as $doc)
            <li>
                <strong>{{ $doc->jenis_dokumen_label }}</strong>
                <small style="color: #666;">({{ $doc->original_filename }})</small>
                @if($doc->status === \App\Models\DokumenPernikahan::STATUS_DIVERIFIKASI)
                <span style="color: green; margin-left: 10px;">✓ Terverifikasi</span>
                @elseif($doc->status === \App\Models\DokumenPernikahan::STATUS_DITOLAK)
                <span style="color: red; margin-left: 10px;">✗ Ditolak</span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Catatan --}}
    @if($pernikahan->catatan_keagamaan || $pernikahan->catatan_admin)
    <div class="section">
        <div class="section-title">CATATAN</div>
        @if($pernikahan->catatan_keagamaan)
        <p><strong>Catatan Keagamaan:</strong> {{ $pernikahan->catatan_keagamaan }}</p>
        @endif
        @if($pernikahan->catatan_admin)
        <p><strong>Catatan Admin:</strong> {{ $pernikahan->catatan_admin }}</p>
        @endif
    </div>
    @endif

    {{-- Signature --}}
    <div class="signature">
        <div class="signature-box">
            <p style="margin-bottom: 60px;">Balige, {{ date('d F Y') }}</p>
            <p><strong>Petugas Keagamaan</strong></p>
            @if(auth()->check())
            <p style="margin-top: 20px;">( {{ auth()->user()->name }} )</p>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Dokumen ini diterbitkan secara elektronik oleh Dinas Kependudukan dan Pencatatan Sipil</p>
        <p>Kabupaten Toba, Sumatera Utara</p>
        <p style="margin-top: 10px; font-size: 9pt;">Dicetak: {{ date('d F Y H:i') }}</p>
    </div>
    {{-- SweetAlert Final Fix --}}
    <script src="{{ asset('js/swal-final-fix.js') }}"></script>
</body>
</html>
