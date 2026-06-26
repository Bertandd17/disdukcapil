@extends('layouts.user')

@section('content')
@php
 use App\Models\Layanan_Model;
 $data_layanan = Layanan_Model::all();

 $jam_kerja = $jam_kerja ?? [
 'senin_kamis' => '08.00 - 16.00 WIB',
 'jumat' => '08.00 - 14.00 WIB',
 'sabtu_minggu' => 'Tutup',
 ];

 $formDownloadAnchor = function (string $filename): string {
    $href = asset('downloads/formulir/' . $filename);
    return '<a href="' . e($href) . '" download="' . e($filename) . '" class="text-blue-600 font-bold hover:underline ml-1"><i class="fas fa-download mr-1"></i>Unduh di Sini</a>';
 };

 $serviceConfig = [
    'kk_perubahan' => [
        'icon'         => 'fa-address-card',
        'color'        => 'blue',
        'id'           => 'kk',
        'persyaratan'  => [
            'Wajib Mengambil Nomor Antrian',
            'Formulir F-1.02 (Formulir Pendaftaran Peristiwa Kependudukan) '.$formDownloadAnchor('F-1.02.pdf'),
            'KTP Pemohon dengan Ukuran Berkas Maksimal 200 KB Berformat PDF',
            'Kartu Keluarga Pemohon dengan Ukuran Berkas Maksimal 200 KB Berformat PDF',
            'Surat keterangan/bukti perubahan Peristiwa Kependudukan (cth: Paspor, SKPWNI) dan Peristiwa Penting.',
            'Formulir F-1.06 (Formulir Pernyataan Perubahan Elemen Data Kependudukan)',
        ],
        'penjelasan'   => [
            'Penduduk mengisi F1.02',
            'Penduduk melampirkan KK',
            'Penduduk mengisi F1.06 karena perubahan elemen data dalam KK',
            'Penduduk melampirkan fotokopi bukti peristiwa kependudukan dan peristiwa penting',
            'Dinas menerbitkan KK Baru',
        ],
        'fields'       => [
            ['name' => 'layanan_id', 'value' => 'kk_perubahan',  'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Informasi Pendaftaran'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'placeholder' => 'Nomor Antrian', 'type' => 'text'],
            ['type' => 'heading', 'label' => 'Data Pemohon'],
            ['name' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'placeholder' => 'Nama Pemohon', 'type' => 'text'],
            ['name' => 'nik_pemohon', 'label' => 'Nomor Induk Kependudukan', 'placeholder' => '16 Digit NIK Pemohon', 'type' => 'text'],
            ['name' => 'nomor_kk_pemohon', 'label' => 'Nomor Kartu Keluarga', 'placeholder' => 'Nomor Kartu Keluarga', 'type' => 'text'],
            ['name' => 'alamat_pemohon', 'label' => 'Alamat', 'placeholder' => 'Alamat Lengkap', 'type' => 'textarea'],
        ],
        'files' => [
            ['name' => 'formulir_f102', 'label' => 'Formulir F1.02'],
            ['name' => 'ktp_pemohon', 'label' => 'KTP Pemohon'],
            ['name' => 'kk_pemohon', 'label' => 'KK Pemohon'],
            ['name' => 'formulir_f106', 'label' => 'Formulir F1.06'],
            ['name' => 'surat_keterangan_perubahan', 'label' => 'Surat Keterangan Bukti Peristiwa Kependudukan dan Peristiwa Penting'],
            ['name' => 'pernyataan_pindah_kk', 'label' => 'Surat Pernyataan Pengasuhan/Wali (Diwajibkan Jika Pindah KK)', 'required' => false],
        ],
    ],
    'kk_ganti_kepala' => [
        'icon'         => 'fa-user-edit',
        'color'        => 'blue',
        'id'           => 'ganti_kepala_kk',
        'persyaratan'  => [
            'Wajib Mengambil Nomor Antrian',
            'Formulir F-1.02 (Formulir Pendaftaran Peristiwa Kependudukan) '.$formDownloadAnchor('F-1.02.pdf'),
            'KTP Pemohon dengan Ukuran Berkas Maksimal 200 KB Berformat PDF',
            'Kartu Keluarga Pemohon dengan Ukuran Berkas Maksimal 200 KB Berformat PDF',
            'Akte Kematian Kepala Keluarga',
            'Surat Pernyataan Bersedia Menjadi Wali',
        ],
        'penjelasan'   => [
            'Penduduk mengisi formulir F-1.02',
            'Melampirkan akta kematian jika kepala keluarga meninggal',
            'Melampirkan KTP dan KK pemohon',
            'Dalam hal seluruh anggota keluarga masih berusia di bawah 17 tahun, diperlukan kepala keluarga yang telah dewasa.',
            'Dinas menerbitkan KK Baru.',
        ],
        'fields'       => [
            ['name' => 'layanan_id', 'value' => 'kk_ganti_kepala', 'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Informasi Pendaftaran'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'placeholder' => 'Nomor Antrian', 'type' => 'text'],
            ['type' => 'heading', 'label' => 'Data Pemohon'],
            ['name' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'placeholder' => 'Nama Pemohon', 'type' => 'text'],
            ['name' => 'nik_pemohon', 'label' => 'Nomor Induk Kependudukan', 'placeholder' => '16 Digit NIK Pemohon', 'type' => 'text'],
            ['name' => 'nomor_kk_pemohon', 'label' => 'Nomor Kartu Keluarga', 'placeholder' => 'Nomor Kartu Keluarga', 'type' => 'text'],
            ['name' => 'alamat_pemohon', 'label' => 'Alamat', 'placeholder' => 'Alamat Lengkap', 'type' => 'textarea'],
        ],
        'files' => [
            ['name' => 'formulir_f102', 'label' => 'Formulir F1.02'],
            ['name' => 'ktp_pemohon', 'label' => 'KTP Pemohon'],
            ['name' => 'kk_pemohon', 'label' => 'KK Pemohon'],
            ['name' => 'akta_kematian', 'label' => 'Akte Kematian Kepala Keluarga Sebelumnya'],
             ['name' => 'surat_pernyataan_wali', 'label' => 'Surat Pernyataan Wali (Jika semua anggota dibawah 17 tahun)', 'required' => false],
        ],
    ],
    'kk_hilang' => [
        'icon'         => 'fa-file-medical-alt',
        'color'        => 'blue',
        'id'           => 'kk_hilang_rusak',
        'persyaratan'  => [
            'Wajib Mengambil Nomor Antrian',
            'Formulir F-1.02 (Formulir Pendaftaran Peristiwa Kependudukan) '.$formDownloadAnchor('F-1.02.pdf'),
            'KTP Pemohon dengan Ukuran Berkas Maksimal 200 KB Berformat PDF',
            'Surat kehilangan dari kepolisian (jika hilang) atau KK yang rusak',
        ],
        'penjelasan'   => [
            'Penduduk mengisi F.1.02',
            'Penduduk menyerahkan dokumen KK yang rusak/surat keterangan kehilangan dari kepolisian',
        ],
        'fields'       => [
            ['name' => 'layanan_id', 'value' => 'kk_hilang', 'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Informasi Pengajuan'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'placeholder' => 'Nomor Antrian', 'type' => 'text'],
            ['type' => 'heading', 'label' => 'Data Pemohon'],
            ['name' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'placeholder' => 'Nama Pemohon', 'type' => 'text'],
            ['name' => 'nik_pemohon', 'label' => 'Nomor Induk Kependudukan', 'placeholder' => '16 Digit NIK Pemohon', 'type' => 'text'],
            ['name' => 'nomor_kk_pemohon', 'label' => 'Nomor Kartu Keluarga', 'placeholder' => 'Nomor Kartu Keluarga', 'type' => 'text'],
            ['name' => 'alamat_pemohon', 'label' => 'Alamat', 'placeholder' => 'Alamat Lengkap', 'type' => 'textarea'],
        ],
        'files' => [
            ['name' => 'formulir_f102', 'label' => 'Formulir F1.02'],
            ['name' => 'ktp_pemohon', 'label' => 'KTP Pemohon'],
            ['name' => 'suket_hilang_rusak', 'label' => 'Surat Kehilangan Kepolisian / Foto KK Rusak'],
        ],
    ],
    'kk_pisah' => [
        'icon'         => 'fa-people-arrows',
        'color'        => 'blue',
        'id'           => 'pisah_kk',
        'persyaratan'  => [
            'Wajib Mengambil Nomor Antrian',
            'Formulir F-1.02 (Formulir Pendaftaran Peristiwa Kependudukan) '.$formDownloadAnchor('F-1.02.pdf'),
            'KK lama',
            'Berumur sekurang-kurangnya 17 (tujuh belas) tahun atau sudah kawin.',
        ],
        'penjelasan'   => [
            'Penduduk mengisi F-1.02',
            'Penduduk melampirkan fotokopi buku nikah atau akta perceraian (jika disebabkan pernikahan atau perceraian)',
            'Penduduk melampirkan KK lama',
        ],
        'fields'       => [
            ['name' => 'layanan_id', 'value' => 'kk_pisah', 'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Informasi Pengajuan'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'placeholder' => 'Nomor Antrian', 'type' => 'text'],
            ['type' => 'heading', 'label' => 'Data Pemohon'],
            ['name' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'placeholder' => 'Nama Pemohon', 'type' => 'text'],
            ['name' => 'nik_pemohon', 'label' => 'Nomor Induk Kependudukan', 'placeholder' => '16 Digit NIK Pemohon', 'type' => 'text'],
            ['name' => 'nomor_kk_pemohon', 'label' => 'Nomor Kartu Keluarga', 'placeholder' => 'Nomor Kartu Keluarga', 'type' => 'text'],
            ['name' => 'alamat_pemohon', 'label' => 'Alamat', 'placeholder' => 'Alamat Lengkap', 'type' => 'textarea'],
        ],
        'files' => [
            ['name' => 'formulir_f102', 'label' => 'Formulir F1.02'],
            ['name' => 'ktp_pemohon', 'label' => 'KTP Pemohon'],
            ['name' => 'kk_pemohon', 'label' => 'KK Pemohon'],
            ['name' => 'fotokopi_buku_nikah', 'label' => 'Buku nikah / akta cerai (Jika karena pernikahan/perceraian)'],
            ['name' => 'kk_lama', 'label' => 'Scan/Foto Asli KK Lama'],
        ],
    ],
    'akte_kelahiran' => [
        'icon'         => 'fa-baby',
        'color'        => 'green',
        'id'           => 'akte_kelahiran',
        'persyaratan'  => [
            'Wajib Mengambil Nomor Antrian',
            'Formulir F-2.01 (Formulir Permohonan Pencatatan Kelahiran) '.$formDownloadAnchor('F-2.01.pdf'),
            'Surat keterangan kelahiran dari rumah sakit/Puskesmas/bidan/kepala desa.',
            'Buku nikah/kutipan akta perkawinan orang tua',
            'KK dan KTP orang tua',
        ],
        'penjelasan'   => [
            'Mengisi formulir F-2.01',
            'Untuk pelayanan online/daring, persyaratan yang discan/difoto untuk diunggah harus aslinya',
        ],
        'fields'       => [
            ['name' => 'layanan_id', 'value' => 'akte_kelahiran', 'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Informasi Pengajuan'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'placeholder' => 'Nomor Antrian', 'type' => 'text'],
            ['type' => 'heading', 'label' => 'Akte Kelahiran'],
            ['name' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'placeholder' => 'Masukkan Nama Pemohon', 'type' => 'text'],
            ['name' => 'nik_pemohon', 'label' => 'NIK Pemohon', 'placeholder' => 'Masukkan NIK Pemohon', 'type' => 'text'],
            ['name' => 'nomor_kk_pemohon', 'label' => 'Nomor KK Pemohon', 'placeholder' => 'Nomor KK Pemohon', 'type' => 'text'],
            ['name' => 'alamat', 'label' => 'Alamat Pemohon', 'placeholder' => 'Alamat Pemohon', 'type' => 'textarea'],
        ],
        'files' => [
            ['name' => 'formulir_f201', 'label' => 'Formulir F2.01 yang Telah Diisi'],
            ['name' => 'ktp_pemohon', 'label' => 'KTP Pemohon'],
            ['name' => 'ktp_saksi1', 'label' => 'KTP Saksi 1'],
            ['name' => 'ktp_saksi2', 'label' => 'KTP Saksi 2'],
            ['name' => 'kk_pemohon', 'label' => 'Kartu Keluarga Pemohon'],
            ['name' => 'file_surat_lahir', 'label' => 'Surat Keterangan Lahir (RS/Bidan/Nakhoda/Kades)'],
            ['name' => 'file_buku_nikah', 'label' => 'Buku Nikah / Akta Perkawinan'],
            ['name' => 'file_sptjm_kelahiran', 'label' => 'SPTJM Kebenaran Data Kelahiran (F-2.03) - Jika tidak ada surat lahir', 'required' => false],
            ['name' => 'file_sptjm_pasutri', 'label' => 'SPTJM Kebenaran Pasangan Suami Istri (F-2.04) - Jika tidak ada buku nikah', 'required' => false],
            ['name' => 'file_berita_acara_polisi', 'label' => 'Berita Acara Kepolisian - Untuk anak tidak diketahui asal usulnya', 'required' => false],
        ],
    ],
    'akte_kematian' => [
        'icon'  => 'fa-user-times',
        'color' => 'orange',
        'id'    => 'akte_kematian',
        'persyaratan' => [
            'Wajib Mengambil Nomor Antrian',
            'Formulir F-2.01 (Formulir Permohonan Pencatatan Kematian) '.$formDownloadAnchor('F-2.01.pdf'),
            'Fotokopi surat kematian dari dokter atau kepala desa/lurah',
            'Fotokopi KK/KTP yang meninggal dunia.',
            'Fotokopi KK/KTP pemohon.',
            'Fotokopi KK/KTP saksi 1 dan saksi 2 yang mengetahui kematian.',
        ],
        'penjelasan' => [
            'Mengisi formulir F-2.01',
            'WNI melampirkan fotokopi KK untuk verifikasi data.',
            'Untuk pelayanan online/Daring, persyaratan yang discan/difoto untuk diunggah harus aslinya.',
            'Seluruh informasi terkait jenazah dan saksi dilampirkan melalui isian Formulir F-2.01.',
        ],
        'fields' => [
            ['name' => 'layanan_id', 'value' => 'akte_kematian', 'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Informasi Pengajuan'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'placeholder' => 'Masukkan Nomor Antrian', 'type' => 'text',],
            ['type' => 'heading', 'label' => 'Data Pemohon'],
            ['name' => 'nik_pemohon', 'label' => 'NIK Pemohon', 'placeholder' => '16 digit NIK Pemohon', 'type' => 'text'],
            ['name' => 'nomor_kk_pemohon', 'label' => 'Nomor KK Pemohon', 'placeholder' => '16 digit Nomor KK', 'type' => 'text'],
            ['name' => 'nama_pemohon', 'label' => 'Nama Lengkap Pemohon', 'placeholder' => 'Masukkan Nama Lengkap Pemohon', 'type' => 'text'],
            ['name' => 'alamat_pemohon', 'label' => 'Alamat Pemohon', 'placeholder' => 'Alamat Domisili', 'type' => 'textarea'],
            ['name' => 'hubungan_pemohon', 'label' => 'Hubungan dengan Jenazah', 'placeholder' => 'Contoh: Anak / Suami / Istri / Ketua RT', 'type' => 'text'],
        ],
        'files' => [
            ['name' => 'formulir_f201', 'label' => 'Scan/Foto Asli Formulir F-2.01 yang telah diisi'],
            ['name' => 'surat_keterangan_kematian', 'label' => 'Scan/Foto Asli Surat Keterangan Kematian (Dokter/Kades)'],
            ['name' => 'ktp_pemohon', 'label' => 'Scan/Foto Asli KTP Pemohon'],
            ['name' => 'kartu_keluarga_pemohon', 'label' => 'Scan/Foto Asli KK Pemohon'],
            ['name' => 'ktp_almarhum', 'label' => 'Scan/Foto Asli KTP Almarhum '],
            ['name' => 'ktp_saksi1', 'label' => 'Scan/Foto Asli KTP Saksi 1 '],
            ['name' => 'ktp_saksi2', 'label' => 'Scan/Foto Asli KTP Saksi 2 '],
        ],
    ],
    'lahir_mati' => [
        'icon'  => 'fa-exclamation-triangle',
        'color' => 'orange',
        'id'    => 'lahir_mati',
        'persyaratan' => [
            'Wajib Mengambil Nomor Antrian',
            'Mengisi Formulir F-2.01 (Formulir Permohonan Pencatatan Kelahiran Mati) '.$formDownloadAnchor('F-2.01.pdf'),
            'Fotokopi surat keterangan lahir mati (RS/Bidan/Kades).',
            'Fotokopi KK Orang Tua.',
            'Fotokopi Saksi 1 dan Saksi 2 yang mengetahui peristiwa lahir mati.',
        ],
        'penjelasan' => [
            'WNI melampirkan fotokopi KK untuk verifikasi data.',
            'Untuk pelayanan online/Daring, persyaratan yang discan/difoto untuk diunggah harus aslinya.',
            'Seluruh informasi terkait jenazah (bayi) dan orang tua dilampirkan melalui isian Formulir F-2.01.',
        ],
        'fields' => [
            ['name' => 'layanan_id', 'value' => 'lahir_mati', 'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Informasi Pengajuan'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'placeholder' => 'Masukkan Nomor Antrian', 'type' => 'text'],
            ['type' => 'heading', 'label' => 'Data Pemohon'],
            ['name' => 'nik_pemohon', 'label' => 'NIK Pemohon', 'placeholder' => '16 digit NIK Pemohon', 'type' => 'text'],
            ['name' => 'nomor_kk_pemohon', 'label' => 'Nomor KK Pemohon', 'placeholder' => '16 digit Nomor KK', 'type' => 'text'],
            ['name' => 'nama_pemohon', 'label' => 'Nama Lengkap Pemohon', 'placeholder' => 'Masukkan Nama Lengkap Pemohon', 'type' => 'text'],
            ['name' => 'alamat_pemohon', 'label' => 'Alamat Pemohon', 'placeholder' => 'Alamat Domisili', 'type' => 'textarea'],
            ['name' => 'hubungan_pemohon', 'label' => 'Hubungan dengan Jenazah Bayi', 'placeholder' => 'Contoh: Ayah / Ibu / Bidan', 'type' => 'text'],
        ],
        'files' => [
            ['name' => 'formulir_f201', 'label' => 'Scan/Foto Asli Formulir F-2.01 yang telah diisi'],
            ['name' => 'surat_keterangan_lahir_mati', 'label' => 'Scan/Foto Asli Surat Ket. Lahir Mati (RS/Bidan/Kades)'],
            ['name' => 'ktp_pemohon', 'label' => 'Scan/Foto Asli KTP Pemohon'],
            ['name' => 'kartu_keluarga_pemohon', 'label' => 'Scan/Foto Asli KK Pemohon'],
            ['name' => 'ktp_saksi1', 'label' => 'Scan/Foto Asli KTP Saksi 1 '],
            ['name' => 'ktp_saksi2', 'label' => 'Scan/Foto Asli KTP Saksi 2 '],
        ],
    ],
    'perkawinan' => [
        'icon'  => 'fa-ring',
        'color' => 'purple',
        'id'    => 'layanan-pernikahan',
        'persyaratan' => [
            'Wajib Mengambil Nomor Antrian terlebih dahulu',
            'KTP kedua calon mempelai (format PDF atau gambar)',
            'KTP kedua saksi (format PDF atau gambar)',
            'Nomor antrian yang masih berlaku (maksimal 24 jam)',
        ],
        'penjelasan' => [
            'Ambil nomor antrian terlebih dahulu di halaman Antrian Online',
            'Isi data pemohon pada langkah 2',
            'Pilih agama dan tempat keagamaan, lalu upload KTP pada langkah 3',
            'Lakukan verifikasi wajah pada langkah 4',
            'Kirim pengajuan dan tunggu konfirmasi dari pihak keagamaan',
        ],
        'is_multi_step' => true,
        'is_pernikahan' => true,
        'steps' => [
            ['label' => 'Informasi', 'icon' => 'fa-info-circle'],
            ['label' => 'Data', 'icon' => 'fa-user'],
            ['label' => 'Berkas', 'icon' => 'fa-file-alt'],
            ['label' => 'Verifikasi', 'icon' => 'fa-shield-alt'],
            ['label' => 'Konfirmasi', 'icon' => 'fa-check'],
        ],
        'fields' => [
            ['name' => 'layanan_id', 'value' => 'perkawinan', 'type' => 'hidden'],
            ['type' => 'heading', 'label' => 'Data Pemohon'],
            ['name' => 'nomor_antrian', 'label' => 'Nomor Antrian', 'type' => 'text', 'placeholder' => 'Contoh: ABC-123-456', 'required' => true],
            ['name' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'type' => 'text', 'placeholder' => 'Nama lengkap sesuai KTP', 'required' => true],
            ['name' => 'nik_pemohon', 'label' => 'NIK Pemohon', 'type' => 'text', 'placeholder' => '16 digit NIK', 'maxlength' => 16, 'required' => true],
             ['name' => 'alamat_pemohon', 'label' => 'Alamat Pemohon', 'type' => 'textarea', 'placeholder' => 'Alamat lengkap', 'required' => true],
        ],
        'files' => [
            ['name' => 'ktp_mempelai_pria', 'label' => 'KTP Mempelai Pria', 'required' => true],
            ['name' => 'ktp_mempelai_wanita', 'label' => 'KTP Mempelai Wanita', 'required' => true],
            ['name' => 'ktp_saksi_1', 'label' => 'KTP Saksi 1', 'required' => true],
            ['name' => 'ktp_saksi_2', 'label' => 'KTP Saksi 2', 'required' => true],
        ],
    ],
 ];

 $kategoriLayanan = [
     'Kartu Keluarga (KK)' => [
         'icon'    => 'fa-id-card',
         'color'   => 'blue',
         'layanan' => ['kk_perubahan', 'kk_ganti_kepala', 'kk_hilang', 'kk_pisah'],
     ],
     'Akte Kelahiran' => [
         'icon'    => 'fa-baby',
         'color'   => 'green',
         'layanan' => ['akte_kelahiran'],
     ],
     'Akte Kematian' => [
         'icon'    => 'fa-file-medical',
         'color'   => 'orange',
         'layanan' => ['akte_kematian', 'lahir_mati'],
     ],
     'Akte Perkawinan' => [
         'icon'    => 'fa-ring',
         'color'   => 'purple',
         'layanan' => ['perkawinan'],
     ],
 ];

 $colorMap = [
     'blue'   => ['bg' => '#EFF6FF', 'text' => '#1D4ED8', 'border' => '#93C5FD', 'badge_bg' => '#DBEAFE', 'badge_text' => '#1E40AF', 'icon_bg' => '#E6F1FB'],
     'green'  => ['bg' => '#F0FDF4', 'text' => '#15803D', 'border' => '#86EFAC', 'badge_bg' => '#DCFCE7', 'badge_text' => '#166534', 'icon_bg' => '#EAF3DE'],
     'orange' => ['bg' => '#FFF7ED', 'text' => '#C2410C', 'border' => '#FDB97D', 'badge_bg' => '#FFEDD5', 'badge_text' => '#9A3412', 'icon_bg' => '#FAEEDA'],
     'purple' => ['bg' => '#FAF5FF', 'text' => '#7E22CE', 'border' => '#D8B4FE', 'badge_bg' => '#F3E8FF', 'badge_text' => '#6B21A8', 'icon_bg' => '#FBEAF0'],
     'red'    => ['bg' => '#FFF1F2', 'text' => '#BE123C', 'border' => '#FDA4AF', 'badge_bg' => '#FFE4E6', 'badge_text' => '#9F1239', 'icon_bg' => '#FCEBEB'],
 ];

 $layananById = \App\Models\Layanan_Model::whereIn('layanan_id', collect($kategoriLayanan)->pluck('layanan')->flatten()->toArray())->get()->keyBy('layanan_id');
@endphp

 {{-- Hero Section --}}
 <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-cyan-800 text-white py-20">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center max-w-3xl mx-auto">
 <h1 class="text-4xl md:text-5xl font-extrabold mb-6">
 Ambil Nomor Antrian dari Rumah
 </h1>
 <p class="text-lg text-green-100 mb-8">
 Tidak perlu datang lebih awal untuk antri. Ambil nomor antrian secara online dan datang sesuai jadwal.
 </p>
 </div>
 </div>

 <div class="absolute bottom-0 left-0 right-0">
 <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
 <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
 </svg>
 </div>
 </section>

 {{-- Jam Operasional Layanan --}}
 <section class="py-8 bg-white">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100">
 <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-0">
 <div class="flex-shrink-0">
 <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
 <i class="fas fa-clock text-white text-xl"></i>
 </div>
 </div>
 <div class="sm:ml-4 flex-1 w-full">
 <h3 class="text-lg font-bold text-gray-800 mb-3">Jam Operasional Layanan</h3>
 <div class="grid md:grid-cols-3 gap-4">
 <div class="bg-white rounded-lg p-4 shadow-sm">
 <div class="flex items-center mb-2">
 <i class="fas fa-calendar-day text-green-600 mr-2"></i>
 <span class="font-semibold text-gray-800">Senin - Kamis</span>
 </div>
 <p class="text-lg font-bold text-green-600">{{ $jam_kerja['senin_kamis'] }}</p>
 </div>
 <div class="bg-white rounded-lg p-4 shadow-sm">
 <div class="flex items-center mb-2">
 <i class="fas fa-calendar-day text-yellow-600 mr-2"></i>
 <span class="font-semibold text-gray-800">Jumat</span>
 </div>
 <p class="text-lg font-bold text-yellow-600">{{ $jam_kerja['jumat'] }}</p>
 </div>
 <div class="bg-white rounded-lg p-4 shadow-sm">
 <div class="flex items-center mb-2">
 <i class="fas fa-calendar-times text-red-600 mr-2"></i>
 <span class="font-semibold text-gray-800">Sabtu - Minggu</span>
 </div>
 <p class="text-lg font-bold text-red-600">{{ $jam_kerja['sabtu_minggu'] }}</p>
 </div>
 </div>
 <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
 <div class="flex items-start gap-2">
 <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
 <div>
 <p class="font-semibold text-yellow-800">Penting:</p>
 <p class="text-sm text-yellow-700">Antrian online hanya dapat dibuat pada jam operasional. Di luar jam kerja, sistem tidak akan menerima permohonan antrian baru.</p>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </section>

 {{-- Alur Pendaftaran Online Section (Revisi 6 Langkah) --}}
 <section id="alur-layanan" class="py-16 bg-white">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-16 reveal">
 <span class="text-blue-600 font-semibold text-sm uppercase tracking-wider">Panduan Masyarakat</span>
 <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Alur Pendaftaran Online</h2>
 <p class="text-gray-600 mt-3 max-w-2xl mx-auto">
 Langkah-langkah mudah mengurus dokumen kependudukan melalui layanan mandiri Disdukcapil Kabupaten
 Toba
 </p>
 </div>

 <div class="relative reveal">
 <div
 class="hidden lg:block absolute top-[45px] left-[8%] right-[8%] h-1 bg-gradient-to-r from-blue-100 via-blue-400 to-blue-100 z-0">
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-10 lg:gap-4 relative z-10">

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-blue-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 1</div>
 <i
 class="fas fa-ticket-alt text-3xl text-blue-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Ambil Antrean</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Dapatkan nomor antrean virtual Anda
 sebelum memulai pengajuan.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-teal-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-teal-500 to-teal-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 2</div>
 <i
 class="fas fa-mouse-pointer text-3xl text-teal-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Pilih Layanan</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Pilih jenis dokumen kependudukan yang
 ingin Anda urus di portal.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-amber-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 3</div>
 <i
 class="fas fa-file-upload text-3xl text-amber-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Unggah Berkas</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Isi formulir elektronik dan unggah
 foto/scan dokumen persyaratan.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-purple-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-purple-500 to-purple-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 4</div>
 <i
 class="fas fa-user-check text-3xl text-purple-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Verifikasi Admin</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Petugas kami akan memvalidasi kebenaran
 dan kelengkapan data Anda.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-indigo-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 5</div>
 <i
 class="fas fa-search text-3xl text-indigo-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Cek Status</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">Pantau terus status pengajuan Anda secara
 berkala menggunakan nomor antrean.</p>
 </div>

 <div class="flex flex-col items-center text-center group">
 <div
 class="w-24 h-24 bg-white rounded-full border-4 border-blue-50 shadow-lg flex items-center justify-center mb-6 relative group-hover:border-rose-500 transition-colors duration-300">
 <div
 class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-rose-500 to-rose-700 text-white font-bold rounded-full flex items-center justify-center border-2 border-white shadow-sm">
 6</div>
 <i
 class="fas fa-cloud-download-alt text-3xl text-rose-600 group-hover:scale-110 transition-transform duration-300"></i>
 </div>
 <h3 class="font-bold text-gray-800 text-base mb-2">Unduh Berkas</h3>
 <p class="text-xs text-gray-600 px-1 leading-relaxed">
 Berkas selesai dikirim ke nomor antrean. Segera unduh sebelum <span
 class="font-bold text-rose-600">batas waktu 1x24 jam</span>.
 </p>
 </div>

 </div>
 </div>
 </div>
 </section>
 
 {{-- Booking Form Section --}}
 <section class="py-16 bg-gray-50" id="formSection">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-12">
 <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Ambil Nomor Antrian</h2>
 <p class="text-gray-600 mt-3 max-w-2xl mx-auto">
 Lengkapi data diri Anda untuk mengambil nomor antrian
 </p>
 </div>

 <div class="bg-white rounded-2xl shadow-lg p-5 sm:p-8">
 {{-- Indikator langkah --}}
 <div class="flex flex-wrap items-center justify-center gap-2 mb-10 text-sm">
 <div class="flex items-center gap-2">
 <span id="step1Indicator" class="step-indicator active flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-bold">1</span>
 <span id="step1Label" class="font-semibold text-blue-600">Upload KTP</span>
 </div>
 <div id="line1" class="w-16 h-1 bg-gray-300 mx-2 rounded hidden sm:block"></div>
 <div class="flex items-center gap-2">
 <span id="step2Indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-bold">2</span>
 <span id="step2Label" class="font-semibold text-gray-400">Verifikasi Data</span>
 </div>
 <div id="line2" class="w-16 h-1 bg-gray-300 mx-2 rounded hidden sm:block"></div>
 <div class="flex items-center gap-3">
 <span id="step3Indicator" class="step-indicator flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-bold">3</span>
 <span id="step3Label" class="font-semibold text-gray-400">Konfirmasi</span>
 </div>
 </div>

 <form id="antrianForm" class="space-y-6" autocomplete="off">
 @csrf

 {{-- STEP 1: Layanan + unggah KTP --}}
 <div id="step1" class="step-content space-y-6">
 <div>
 <label class="block text-lg font-semibold text-gray-700 mb-2">
 Jenis Layanan <span class="text-red-500">*</span>
 </label>
 <select name="layanan_id" id="layanan_id" data-validate-security="true"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base bg-white">
 <option value="" disabled selected>Pilih jenis layanan...</option>
 @foreach($data_layanan as $layanan)
 <option value="{{ $layanan->layanan_id }}">{{ $layanan->nama_layanan }}</option>
 @endforeach
 </select>
 </div>

 <input type="file" id="ktpFileInput" accept="image/jpeg,image/jpg,image/png,image/pjpeg" class="hidden" aria-hidden="true">

 <div>
 <label class="block text-lg font-semibold text-gray-700 mb-2">
 Foto e-KTP <span class="text-red-500">*</span>
 </label>
 <div id="uploadArea" class="relative border-2 border-dashed border-gray-300 rounded-2xl p-5 sm:p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/40 transition-colors">
 <div id="uploadPlaceholder">
 <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-4"></i>
 <p class="text-gray-700 font-medium mb-1">Klik atau seret foto KTP ke sini</p>
 <p class="text-sm text-gray-500">JPG, JPEG, atau PNG, maks. 5 MB</p>
 <p id="uploadDebug" class="text-xs text-gray-400 mt-3">Status: <span id="uploadDebugValue">memuat...</span></p>
 </div>
 <div id="previewContainer" class="hidden">
 <img id="imagePreview" src="" alt="Pratinjau KTP" class="max-h-56 mx-auto rounded-lg shadow-md object-contain">
 <p id="fileName" class="text-sm text-gray-600 mt-3 font-medium"></p>
 <button type="button" id="changeImageBtn" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-semibold underline">
 Ganti foto
 </button>
 </div>
 </div>
 </div>

<button type="button" id="step1NextBtn" disabled
 class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
 <i class="fas fa-arrow-right mr-2"></i>
 Lanjut dan Kirim ke OCR
</button>
 </div>

 {{-- STEP 2: Hasil OCR + koreksi --}}
 <div id="step2" class="step-content hidden space-y-6">
 <div id="ocrConfidence" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
 <div class="flex gap-3">
 <div id="ocrStatusIcon" class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
 <i id="ocrStatusFa" class="fas fa-check-circle text-blue-600"></i>
 </div>
 <div class="flex-1 min-w-0">
 <div class="flex flex-wrap items-center gap-2 mb-1">
 <span id="ocrStatusTitle" class="font-semibold text-blue-800">Data berhasil diekstrak</span>
 <span id="ocrTrustBadge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-200 text-blue-800">Auto-fill</span>
 </div>
 <p id="ocrStatusMessage" class="text-sm text-blue-900">Data dari foto KTP sudah diisi otomatis. Silakan periksa dan koreksi jika perlu.</p>
 </div>
 </div>
 </div>

 {{-- Data Wajib --}}
 <div class="grid md:grid-cols-2 gap-4">
 <div>
 <label class="block font-semibold text-gray-700 mb-2">NIK <span class="text-red-500">*</span></label>
 <input type="text" name="nik" id="nik" inputmode="numeric" maxlength="16" placeholder="16 digit" data-validate-security="true"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-base">
 </div>
 <div>
 <label class="block font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
 <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Sesuai KTP" data-validate-security="true"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-base">
 </div>
 </div>

 <div>
 <label class="block font-semibold text-gray-700 mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
 <textarea name="alamat" id="alamat" rows="3" placeholder="Alamat pada KTP" data-validate-security="true"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none text-base"></textarea>
 </div>

 <div class="flex flex-col sm:flex-row gap-3">
 <button type="button" id="step2PrevBtn"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition">
 <i class="fas fa-arrow-left mr-2"></i> Kembali
 </button>
 <button type="button" id="step2NextBtn"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg">
 Lanjut ke konfirmasi <i class="fas fa-arrow-right ml-2"></i>
 </button>
 </div>
 </div>

 {{-- STEP 3: Ringkasan --}}
 <div id="step3" class="step-content hidden space-y-6">
 <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 space-y-4 text-left">
 <h3 class="font-bold text-gray-800 text-lg border-b pb-2">Ringkasan data</h3>
 <dl class="grid gap-3 text-sm">
 <div class="flex justify-between gap-4"><dt class="text-gray-500">NIK</dt><dd id="summaryNik" class="font-mono font-semibold text-gray-900 text-right break-all">-</dd></div>
 <div class="flex justify-between gap-4"><dt class="text-gray-500">Nama</dt><dd id="summaryNama" class="font-semibold text-gray-900 text-right">-</dd></div>
 <div class="flex justify-between gap-4 items-start"><dt class="text-gray-500 shrink-0">Alamat</dt><dd id="summaryAlamat" class="text-gray-900 text-right">-</dd></div>
 <div class="flex justify-between gap-4"><dt class="text-gray-500">Layanan</dt><dd id="summaryLayanan" class="font-semibold text-gray-900 text-right">-</dd></div>
 <div class="flex justify-between gap-4"><dt class="text-gray-500">Nomor antrian (sementara)</dt><dd id="summaryNomor" class="font-mono font-bold text-green-700 text-right">-</dd></div>
 </dl>
 </div>
 <div class="flex flex-col sm:flex-row gap-3">
 <button type="button" id="step3PrevBtn"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition">
 <i class="fas fa-arrow-left mr-2"></i> Ubah data
 </button>
 <button type="submit" id="submitBtn"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg">
 <i class="fas fa-check-circle mr-2"></i>
 Konfirmasi dan dapatkan nomor antrian
 </button>
 </div>
 </div>
 </form>
 </div>
 </div>
 </section>

 {{-- Ticket Result Section dengan Animasi --}}
 <section id="ticketResult" class="py-16 bg-gray-50 hidden">
 <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
 <!-- Confetti Container -->
 <div id="confetti-container" class="fixed inset-0 pointer-events-none z-50"></div>

 <div class="bg-white rounded-2xl shadow-2xl overflow-hidden ticket-wrapper">
 <!-- Header Tiket -->
 <div class="bg-gradient-to-r from-green-600 via-emerald-600 to-green-700 text-white p-8 text-center relative overflow-hidden">
 <!-- Animated Background -->
 <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer"></div>

 <div class="relative z-10">
 <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-4 animate-bounce-slow">
 <i class="fas fa-ticket-alt text-5xl"></i>
 </div>
 <h3 class="text-3xl font-bold mb-2">Nomor Antrian Anda</h3>
 <p class="text-green-100">Simpan nomor ini untuk mengecek status</p>
 </div>
 </div>

 <!-- Body Tiket -->
 <div class="p-5 sm:p-8 text-center relative">
 <!-- Nomor Antrian dengan Counter Animation -->
 <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-5 sm:p-8 mb-6 relative overflow-hidden">
 <div class="absolute inset-0 bg-gradient-to-r from-green-600/5 to-emerald-600/5 animate-pulse-slow"></div>
 <div class="relative z-10">
 <p class="text-sm text-gray-500 mb-2 font-medium">NOMOR ANTRIAN</p>
 <div class="text-7xl font-black bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent mb-4 counter-animate" id="ticketNumber">ABC-123</div>
 <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
 <i class="fas fa-clock"></i>
 <span id="ticketTime">-</span>
 </div>
 </div>
 </div>

 <!-- Info Grid -->
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left mb-6">
 <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100 info-card">
 <div class="flex items-center gap-2 mb-2">
 <i class="fas fa-user text-green-600"></i>
 <p class="text-xs font-semibold text-gray-500 uppercase">Nama</p>
 </div>
 <p class="font-bold text-gray-800 text-lg" id="ticketName">-</p>
 </div>
 <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100 info-card">
 <div class="flex items-center gap-2 mb-2">
 <i class="fas fa-file-alt text-purple-600"></i>
 <p class="text-xs font-semibold text-gray-500 uppercase">Layanan</p>
 </div>
 <p class="font-bold text-gray-800 text-lg" id="ticketService">-</p>
 </div>
 </div>

 <!-- Action Buttons -->
 <div class="flex flex-col sm:flex-row gap-3">
 <button onclick="navigateToLayanan()" id="goToLayananBtn" class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg action-btn no-print">
 <i class="fas fa-arrow-right mr-2"></i>
 Menuju Layanan
 </button>
 </div>
 </div>

 <!-- Decorative Elements -->
 <div class="absolute top-0 left-0 w-32 h-32 bg-green-500/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
 <div class="absolute bottom-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full translate-x-1/2 translate-y-1/2"></div>
 </div>
 </div>
 </section>

 {{-- Layanan Mandiri Section --}}
 <section class="py-12 bg-white relative z-10" id="layananMandiriSection">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-10 reveal">
 <h2 class="text-3xl font-extrabold text-gray-800">Layanan Mandiri</h2>
 <p class="text-gray-500 mt-2 text-sm">Pilih jenis layanan kependudukan yang Anda butuhkan (Persyaratan: Nomor Antrian)</p>
 </div>

 <div class="mb-8 bg-blue-50 border border-blue-200 rounded-2xl p-5 reveal shadow-sm">
 <div class="flex items-start gap-4">
 <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-200">
 <i class="fas fa-info-circle text-lg text-white"></i>
 </div>
 <div>
 <h4 class="font-bold text-gray-800 text-sm mb-1">Panduan Pengajuan</h4>
 <p class="text-gray-600 text-sm leading-relaxed">
 Pilih kategori layanan, lalu pilih jenis layanan yang sesuai kebutuhan Anda.
 Pastikan Anda sudah mengambil nomor antrian terlebih dahulu sebelum mengisi formulir.
 </p>
 </div>
 </div>
 </div>

 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 reveal">
 @foreach($kategoriLayanan as $namaKategori => $kategoriConfig)
 @php
 $c = $colorMap[$kategoriConfig['color']] ?? $colorMap['blue'];
 $jumlah = count($kategoriConfig['layanan']);

 $layananList = [];
 foreach ($kategoriConfig['layanan'] as $lid) {
 $layanan = $layananById[$lid] ?? null;
 $config  = $serviceConfig[$lid] ?? null;
 if (!$layanan || !$config) continue;
 $layananList[] = [
 'lid'      => $lid,
 'name'     => $layanan->nama_layanan,
 'desc'     => $layanan->keterangan ?? str_replace('Penerbitan ', '', $layanan->nama_layanan),
 'icon'     => $config['icon'],
 'config'   => $config,
 ];
 }
 $layananListJson = json_encode($layananList);
 @endphp

 <button
 data-style-guide-skip
 onclick='openKategoriModal({{ $layananListJson }}, {{ json_encode($namaKategori) }}, {{ json_encode($c) }}, {{ json_encode($kategoriConfig["icon"]) }})'
 class="group bg-white rounded-2xl p-5 text-left border-2 border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col"
 style="min-height: 140px; background: #ffffff;"
 onmouseover="this.style.borderColor='{{ $c['border'] }}'"
 onmouseout="this.style.borderColor='#F3F4F6'">

 <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3 flex-shrink-0"
 style="background: {{ $c['icon_bg'] }}">
 <i class="fas {{ $kategoriConfig['icon'] }} text-xl" style="color: {{ $c['text'] }}"></i>
 </div>
 <div class="flex-1">
 <h3 class="font-bold text-gray-800 text-sm mb-1 leading-tight">{{ $namaKategori }}</h3>
 <p class="text-xs text-gray-400">{{ $jumlah }} layanan tersedia</p>
 </div>
 <div class="mt-3">
 <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold"
 style="background: {{ $c['badge_bg'] }}; color: {{ $c['badge_text'] }}">
 <i class="fas fa-arrow-right text-[10px]"></i> Lihat Layanan
 </span>
 </div>
 </button>
 @endforeach
 </div>
 </div>
 </section>


 {{-- Modal Kategori --}}
 <div id="kategoriModal" class="fixed inset-0 z-40 hidden overflow-y-auto">
 <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeKategoriModal()"></div>
 <div class="flex items-start sm:items-center justify-center min-h-screen p-3 sm:p-4">
 <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden" style="animation: popIn 0.2s ease;">
 <div id="km-header" class="p-5 border-b border-gray-100">
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-3">
 <div id="km-icon" class="w-10 h-10 rounded-xl flex items-center justify-center"></div>
 <div>
 <h3 id="km-title" class="text-base font-bold text-gray-800"></h3>
 <p id="km-sub" class="text-xs text-gray-400 mt-0.5"></p>
 </div>
 </div>
 <button onclick="closeKategoriModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
 <i class="fas fa-times text-gray-500 text-sm"></i>
 </button>
 </div>
 </div>
 <div id="km-list" class="p-3 space-y-1 max-h-[60vh] overflow-y-auto"></div>
 <div class="px-5 py-3 border-t border-gray-100">
 <p class="text-xs text-gray-400 text-center">Siapkan berkas pendukung pada halaman selanjutnya dalam format PDF dengan ukuran maksimal 200 KB per file.</p>
 </div>
 </div>
 </div>
 </div>

 {{-- Modal Service --}}
 <div id="serviceModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
 <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
 <div class="flex items-start sm:items-center justify-center min-h-screen p-2 sm:p-4">
 <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-2xl w-full max-h-[96vh] sm:max-h-[92vh] overflow-y-auto transform transition-all relative z-10" id="lmModalContent">
 {{-- Sticky Header + Steps --}}
 <div id="lmModalHeader" class="sticky top-0 z-20 bg-white p-5 border-b border-gray-100 rounded-t-3xl">
 <div class="flex items-center justify-between mb-4">
 <div class="flex items-center gap-3 min-w-0">
 <div id="lmModalIcon" class="w-11 h-11 rounded-xl flex items-center justify-center"></div>
 <div class="min-w-0">
 <h3 id="lmModalTitle" class="text-base sm:text-lg font-bold text-gray-800 leading-snug"></h3>
 <p id="lmModalStepLabel" class="text-xs text-gray-400 font-medium"></p>
 </div>
 </div>
 <button onclick="closeModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-gray-100 hover:bg-gray-200 transition flex-shrink-0">
 <i class="fas fa-times text-gray-500 text-sm"></i>
 </button>
 </div>
 {{-- Step Indicators --}}
 <div class="flex items-center gap-1">
 @foreach(['Informasi','Data','Berkas','Verifikasi','Konfirmasi'] as $i => $lmStepName)
 <div class="flex-1 flex flex-col items-center">
 <div class="lm-step-indicator w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold mb-1 transition-all duration-300"
 id="lmStepDot{{ $i+1 }}" data-step="{{ $i+1 }}">{{ $i+1 }}</div>
 <span class="text-[9px] font-semibold lm-step-label text-gray-400" id="lmStepLabel{{ $i+1 }}">{{ $lmStepName }}</span>
 </div>
 @if($i < 4)
 <div class="flex-1 h-0.5 bg-gray-200 rounded mb-5" id="lmStepLine{{ $i+1 }}"></div>
 @endif
 @endforeach
 </div>
 </div>

 @if($errors->any())
 <div class="mx-5 mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl">
 <div class="flex items-center mb-2">
 <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
 <span class="text-red-800 font-bold text-sm">Terjadi Kesalahan Validasi:</span>
 </div>
 <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
 @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
 </ul>
 </div>
 @endif

 <form id="lmServiceForm" method="POST" action="{{ route('pernikahan.store.layanan-mandiri') }}" enctype="multipart/form-data">
 @csrf
 <input type="hidden" name="foto_wajah" id="lm_foto_wajah">

 {{-- Step 1: Informasi --}}
 <div id="lmStep1" class="step-content p-5 space-y-5">
 <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
 <div class="flex items-center gap-2 mb-2">
 <i class="fas fa-info-circle text-blue-600"></i>
 <h4 class="font-bold text-blue-800 text-sm">Informasi Layanan</h4>
 </div>
 <p id="lmInfoLayanan" class="text-sm text-blue-700 leading-relaxed"></p>
 </div>
 <div>
 <div class="flex items-center gap-2 mb-3">
 <div class="w-6 h-6 bg-orange-100 rounded-lg flex items-center justify-center">
 <i class="fas fa-list-check text-orange-600 text-xs"></i>
 </div>
 <h4 class="font-bold text-gray-800 text-sm">Persyaratan Dokumen yang Dibutuhkan</h4>
 </div>
 <ul id="lmListPersyaratan" class="space-y-2"></ul>
 </div>
 <div>
 <div class="flex items-center gap-2 mb-3">
 <div class="w-6 h-6 bg-green-100 rounded-lg flex items-center justify-center">
 <i class="fas fa-route text-green-600 text-xs"></i>
 </div>
 <h4 class="font-bold text-gray-800 text-sm">Alur Pengajuan</h4>
 </div>
 <ol id="lmListPenjelasan" class="space-y-2"></ol>
 </div>
 <button type="button" onclick="lmGoToStep(2)"
 class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg flex items-center justify-center gap-2">
 Selanjutnya <i class="fas fa-arrow-right text-sm"></i>
 </button>
 </div>

 {{-- Step 2: Data --}}
 <div id="lmStep2" class="step-content p-5 space-y-4 hidden">
 <p class="text-sm text-gray-500 mb-1">Masukkan NIK atau nomor antrian terlebih dahulu. Data pemohon akan terisi otomatis dari sistem.</p>
 <div id="lmFormFields" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
 <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-4 border-t border-gray-100">
 <button type="button" onclick="lmGoToStep(1)"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition flex items-center justify-center gap-2">
 <i class="fas fa-arrow-left text-sm"></i> Kembali
 </button>
 <button type="button" onclick="lmValidateAndGoStep3()"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg flex items-center justify-center gap-2">
 Selanjutnya <i class="fas fa-arrow-right text-sm"></i>
 </button>
 </div>
 </div>

 {{-- Step 3: Berkas --}}
 <div id="lmStep3" class="step-content p-5 space-y-4 hidden">
 <p id="lmStep3Description" class="text-sm text-gray-500 mb-1">Upload berkas persyaratan dalam format <strong>PDF</strong>. Pastikan dokumen terbaca dengan jelas.</p>
 <div id="lmFileFields" class="space-y-4"></div>
 <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-4 border-t border-gray-100">
 <button type="button" onclick="lmGoToStep(2)"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition flex items-center justify-center gap-2">
 <i class="fas fa-arrow-left text-sm"></i> Kembali
 </button>
 <button type="button" onclick="lmValidateAndGoStep4()"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg flex items-center justify-center gap-2">
 Selanjutnya <i class="fas fa-arrow-right text-sm"></i>
 </button>
 </div>
 </div>

 {{-- Step 4: Verifikasi --}}
 <div id="lmStep4" class="step-content p-5 space-y-4 hidden">
 <h3 class="font-bold text-lg text-gray-800">Verifikasi Wajah</h3>
 <p class="text-sm text-gray-500">Kedipkan mata <strong>2 kali</strong> di depan kamera untuk membuktikan Anda bukan robot.</p>
 <div class="relative rounded-2xl overflow-hidden border-2 border-gray-200 bg-black">
 <video id="lmVideo" autoplay playsinline muted class="w-full rounded-xl" style="max-height:260px; object-fit:cover;"></video>
 <canvas id="lmCanvas" class="hidden"></canvas>
 <div id="lmLivenessOverlay" class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-center py-2 text-sm font-semibold">
 Tekan "Mulai Verifikasi" untuk mengaktifkan kamera
 </div>
 </div>
 <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
 <div class="flex gap-2">
 <span id="lmBlinkDot1" class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center text-xs font-bold text-gray-400">1</span>
 <span id="lmBlinkDot2" class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center text-xs font-bold text-gray-400">2</span>
 </div>
 <div>
 <p class="text-sm font-semibold text-gray-700">Kedipan terdeteksi: <span id="lmBlinkCount">0</span>/2</p>
 <p class="text-xs text-gray-400">Kedipkan secara natural, jangan terlalu cepat</p>
 </div>
 </div>
 <input type="hidden" name="liveness_passed" id="lm_liveness_passed" value="0">
 <div id="lmLivenessError" class="hidden bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700"></div>
 <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mt-4 text-xs">
 <p class="text-gray-600"><i class="fas fa-video mr-1"></i>Dengan menggunakan kamera, Anda menyetujui kebijakan akses kamera untuk liveness check.</p>
 </div>
 <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
 <button type="button" onclick="lmGoToStep(3); stopCamera();"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition flex items-center justify-center gap-2">
 <i class="fas fa-arrow-left text-sm"></i> Kembali
 </button>
 <button type="button" id="lmBtnStartLiveness" onclick="handleLivenessAction()"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg flex items-center justify-center gap-2">
 <i class="fas fa-camera text-sm"></i> Mulai Verifikasi
 </button>
 </div>
 </div>

 {{-- Step 5: Konfirmasi --}}
 <div id="lmStep5" class="step-content p-5 space-y-4 hidden">
 <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
 <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
 <i class="fas fa-check-circle text-green-600 text-2xl"></i>
 </div>
 <h4 class="font-bold text-green-800 text-base mb-1">Data Siap Dikirim</h4>
 <p class="text-green-700 text-sm">Pastikan semua data dan berkas yang Anda isi sudah benar sebelum mengirim pengajuan.</p>
 </div>
 <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
 <h5 class="font-bold text-gray-700 text-sm mb-3 flex items-center gap-2">
 <i class="fas fa-clipboard-list text-blue-500"></i> Ringkasan Pengajuan
 </h5>
 <div id="lmSummaryData" class="space-y-2 text-sm text-gray-600"></div>
 </div>
 <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-4 border-t border-gray-100">
 <button type="button" onclick="lmGoToStep(4)"
 class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold transition flex items-center justify-center gap-2">
 <i class="fas fa-arrow-left text-sm"></i> Kembali
 </button>
 <button type="button" id="lmBtnSubmit" onclick="handleKirimPengajuan()"
 class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg flex items-center justify-center gap-2">
 <i class="fas fa-paper-plane text-sm"></i> Kirim Pengajuan
 </button>
 </div>
 </div>
 </form>
 </div>
 </div>
 </div>

 {{-- Cari Antrian Section --}}
 <section class="py-16 bg-white">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-12">
 <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Lacak Status Berkas</h2>
 <p class="text-gray-600 mt-3">Cari status berkas Anda dengan memasukkan NIK atau nomor antrian</p>
 </div>

 <div class="bg-gradient-to-br from-gray-50 to-emerald-50 rounded-2xl shadow-lg p-5 sm:p-8 border border-gray-100">
 <div class="grid md:grid-cols-3 gap-4 mb-6">
 <div class="md:col-span-2">
 <input type="text" id="searchInput" placeholder="Masukkan NIK (16 digit) atau nomor antrian" inputmode="text" maxlength="20" data-validate-security="true"
 class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
 </div>
 <div>
 <select id="searchLayanan" data-validate-security="true" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white">
 <option value="">Semua Layanan</option>
 @foreach($data_layanan as $layanan)
 <option value="{{ $layanan->layanan_id }}">{{ $layanan->nama_layanan }}</option>
 @endforeach
 </select>
 </div>
 </div>
 <button type="button" id="btnCariAntrian" class="w-full py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 transition-all shadow-lg">
 <i class="fas fa-search mr-2"></i>
 Cari Antrian
 </button>
 </div>

 <!-- Search Results dengan Staggered Animation -->
 <div id="searchResults" class="mt-8 space-y-4"></div>
 </div>
 </section>

  <section class="py-12 bg-gray-50">
 <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-10 reveal">
 <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Keuntungan Layanan Mandiri</h2>
 </div>
 <div class="grid md:grid-cols-3 gap-6 reveal">
 <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 text-center border border-blue-200">
 <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3">
 <i class="fas fa-clock text-2xl text-white"></i>
 </div>
 <h3 class="font-bold text-gray-800 mb-2">Hemat Waktu</h3>
 <p class="text-gray-600 text-sm">Tanpa antri di kantor dukcapil</p>
 </div>
 <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 text-center border border-purple-200">
 <div class="w-14 h-14 bg-purple-600 rounded-xl flex items-center justify-center mx-auto mb-3">
 <i class="fas fa-home text-2xl text-white"></i>
 </div>
 <h3 class="font-bold text-gray-800 mb-2">Dari Mana Saja</h3>
 <p class="text-gray-600 text-sm">Proses online 24 jam</p>
 </div>
 <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 text-center border border-green-200">
 <div class="w-14 h-14 bg-green-600 rounded-xl flex items-center justify-center mx-auto mb-3">
 <i class="fas fa-check-circle text-2xl text-white"></i>
 </div>
 <h3 class="font-bold text-gray-800 mb-2">Pantau Status</h3>
 <p class="text-gray-600 text-sm">Update status secara real-time</p>
 </div>
 </div>
 </div>
 </section>

@endsection

@push('styles')
<style>
    /* Layanan Mandiri Styles */
    .lm-step-indicator { background: #e5e7eb; color: #9ca3af; }
    .lm-step-indicator.active { background: #2563eb; color: #fff; box-shadow: 0 0 0 3px #bfdbfe; }
    .lm-step-indicator.done { background: #16a34a; color: #fff; }
    .lm-step-label.active { color: #2563eb !important; }
    .lm-step-label.done { color: #16a34a !important; }
    #lmStepLine1.done, #lmStepLine2.done, #lmStepLine3.done, #lmStepLine4.done { background: #16a34a; }

    .form-input {
        width: 100%; padding: 0.6rem 1rem;
        border: 2px solid #e5e7eb; border-radius: 0.75rem;
        font-size: 0.875rem; transition: border-color 0.2s;
        outline: none; background: #fff;
    }
    .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px #dbeafe; }
    .form-input.form-input--readonly,
    .form-input[readonly] {
        background: #f3f4f6;
        color: #374151;
        cursor: not-allowed;
        border-color: #e5e7eb;
    }
    .form-input.form-input--readonly:focus,
    .form-input[readonly]:focus {
        border-color: #e5e7eb;
        box-shadow: none;
    }
    .form-input.loading {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Ccircle cx='12' cy='12' r='10' stroke='%233b82f6' stroke-width='4' fill='none' opacity='0.25'/%3E%3Cpath d='M12 2a10 10 0 0 1 10 10' stroke='%233b82f6' stroke-width='4' fill='none'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='1s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 1.2rem;
    }
    .layanan-item { transition: background 0.15s; }
    .layanan-item:hover { background: #f9fafb; }

    @media (max-width: 640px) {
        #lmModalHeader { padding: 1rem; }
        #lmModalIcon, #km-icon { flex-shrink: 0; }
        #lmModalTitle, #km-title { overflow-wrap: anywhere; }
        #lmModalHeader .lm-step-label { display: none; }
        #lmModalHeader .lm-step-indicator { width: 1.8rem; height: 1.8rem; margin-bottom: 0; }
        #serviceModal .step-content { padding: 1rem; }
        #lmSummaryData .flex { align-items: flex-start; gap: 0.75rem; }
        #lmSummaryData span:last-child { max-width: 55%; white-space: normal; overflow-wrap: anywhere; }
    }

    .swal2-loading .swal2-actions,
    .swal2-loading .swal2-confirm,
    .swal2-loading .swal2-deny,
    .swal2-loading .swal2-cancel,
    .swal2-loading .swal2-styled,
    .swal2-show.swal2-loading .swal2-actions {
        display: none !important;
    }
    .swal2-container .swal2-loading + .swal2-actions {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        overflow: hidden !important;
    }

 /* Ticket Animation */
 .ticket-wrapper {
 animation: ticketAppear 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
 }

 @keyframes ticketAppear {
 0% {
 transform: scale(0.3) rotate(-10deg);
 opacity: 0;
 }
 50% {
 transform: scale(1.05) rotate(2deg);
 }
 100% {
 transform: scale(1) rotate(0deg);
 opacity: 1;
 }
 }

 /* Counter Animation untuk Nomor Antrian */
 .counter-animate {
 animation: counterPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.3s both;
 }

 @keyframes counterPop {
 0% {
 transform: scale(0);
 opacity: 0;
 }
 50% {
 transform: scale(1.2);
 }
 100% {
 transform: scale(1);
 opacity: 1;
 }
 }

 /* Info Cards Staggered Animation */
 .info-card {
 animation: slideUp 0.5s ease-out 0.4s both;
 }

 .info-card:nth-child(1) {
 animation-delay: 0.4s;
 }

 .info-card:nth-child(2) {
 animation-delay: 0.5s;
 }

 @keyframes slideUp {
 from {
 transform: translateY(20px);
 opacity: 0;
 }
 to {
 transform: translateY(0);
 opacity: 1;
 }
 }

 /* Action Buttons */
 .action-btn {
 animation: buttonSlide 0.5s ease-out 0.6s both;
 }

 @keyframes buttonSlide {
 from {
 transform: translateY(10px);
 opacity: 0;
 }
 to {
 transform: translateY(0);
 opacity: 1;
 }
 }

 /* Shimmer Effect */
 @keyframes shimmer {
 0% {
 transform: translateX(-100%);
 }
 100% {
 transform: translateX(100%);
 }
 }

 .animate-shimmer > div {
 animation: shimmer 2s infinite;
 }

 /* Bounce Animation */
 @keyframes bounce-slow {
 0%, 100% {
 transform: translateY(0);
 }
 50% {
 transform: translateY(-10px);
 }
 }

 .animate-bounce-slow {
 animation: bounce-slow 2s ease-in-out infinite;
 }

 /* Pulse Slow Animation */
 @keyframes pulse-slow {
 0%, 100% {
 opacity: 1;
 }
 50% {
 opacity: 0.7;
 }
 }

 .animate-pulse-slow {
 animation: pulse-slow 2s ease-in-out infinite;
 }

 /* Search Result Card Animation */
 .search-result-card {
 animation: cardSlideIn 0.5s ease-out both;
 }

 @keyframes cardSlideIn {
 from {
 transform: translateX(-30px);
 opacity: 0;
 }
 to {
 transform: translateX(0);
 opacity: 1;
 }
 }

 /* Lacak Card Animation */
 .lacak-card {
 animation: lacakAppear 0.7s cubic-bezier(0.68, -0.55, 0.265, 1.55);
 }

 @keyframes lacakAppear {
 0% {
 transform: scale(0.8) translateY(20px);
 opacity: 0;
 }
 100% {
 transform: scale(1) translateY(0);
 opacity: 1;
 }
 }

 /* Timeline Animation */
 .timeline-item {
 animation: timelineFade 0.5s ease-out both;
 }

 @keyframes timelineFade {
 from {
 transform: translateX(-20px);
 opacity: 0;
 }
 to {
 transform: translateX(0);
 opacity: 1;
 }
 }

 /* Timeline Progress Line Animation */
 .timeline-progress {
 animation: progressLine 1.5s ease-out forwards;
 }

 @keyframes progressLine {
 from {
 height: 0;
 }
 to {
 height: 100%;
 }
 }

 /* Status Badge Glow */
 .status-glow {
 animation: glow 2s ease-in-out infinite;
 }

 @keyframes glow {
 0%, 100% {
 box-shadow: 0 0 5px currentColor;
 }
 50% {
 box-shadow: 0 0 20px currentColor, 0 0 30px currentColor;
 }
 }

 /* Confetti Animation */
 .confetti {
 position: fixed;
 width: 10px;
 height: 10px;
 top: -10px;
 animation: confettiFall 3s linear forwards;
 }

 @keyframes confettiFall {
 0% {
 transform: translateY(0) rotate(0deg);
 opacity: 1;
 }
 100% {
 transform: translateY(100vh) rotate(720deg);
 opacity: 0;
 }
 }

 /* Stat Cards */
 .stat-card {
 transition: all 0.3s ease;
 }

 .stat-card:hover {
 transform: translateY(-5px);
 box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
 }

 @media (max-width: 640px) {
 #step1Indicator,
 #step2Indicator,
 #step3Indicator {
 width: 2rem;
 height: 2rem;
 font-size: 0.8rem;
 }

 #step1Label,
 #step2Label,
 #step3Label {
 font-size: 0.75rem;
 }

 #ticketNumber {
 font-size: clamp(2.5rem, 14vw, 4rem);
 line-height: 1;
 overflow-wrap: anywhere;
 }

 .search-result-card > div:first-child,
 .search-result-card .flex.items-center.gap-2.mb-3 {
 align-items: flex-start;
 }

 .search-result-card .flex.items-center.gap-2.px-3.py-2 {
 align-self: flex-start;
 max-width: 100%;
 }

 .search-result-card .grid.grid-cols-2 {
 grid-template-columns: minmax(0, 1fr) !important;
 }

 .swal2-popup .flex.justify-between {
 gap: 0.75rem;
 }
 }

 /* Print Styles */
 @media print {
 /* Sembunyikan semua elemen kecuali tiket */
 body > *:not(#ticketResult):not(#lacakResult) {
 display: none !important;
 }

 /* Tampilkan section yang relevan */
 #ticketResult,
 #lacakResult {
 display: block !important;
 position: absolute;
 left: 0;
 top: 0;
 width: 100%;
 margin: 0 !important;
 padding: 20px !important;
 }

 /* Hilangkan elemen dekoratif dan tombol */
 .no-print,
 #confetti-container,
 .action-btn,
 #ticketResult .absolute:not(.bg-gradient-to-r):not(.inset-0) {
 display: none !important;
 }

 /* Style untuk cetak tiket */
 .ticket-wrapper {
 box-shadow: none !important;
 border: 2px solid #000 !important;
 page-break-inside: avoid;
 max-width: 100% !important;
 }

 /* Style untuk cetak lacak result */
 .lacak-card {
 box-shadow: none !important;
 border: 1px solid #000 !important;
 page-break-inside: avoid;
 }

 .bg-gradient-to-r {
 background: #28A745 !important;
 -webkit-print-color-adjust: exact;
 print-color-adjust: exact;
 }

 /* Pastikan warna tercetak dengan benar */
 * {
 -webkit-print-color-adjust: exact !important;
 print-color-adjust: exact !important;
 }

 /* Hentikan semua animasi saat print */
 * {
 animation: none !important;
 transition: none !important;
 transform: none !important;
 }

 /* Atur ukuran kertas */
 @page {
 size: A4;
 margin: 15mm;
 }

 body {
 margin: 0;
 padding: 0;
 background: white !important;
 }

 /* Pastikan text tetap terbaca */
 .text-transparent {
 background-clip: border-box !important;
 -webkit-background-clip: border-box !important;
 color: #28A745 !important;
 }
 }
</style>
@endpush

@push('scripts')
<script>
 window.ANTRIAN_OCR_CONFIG = @json($ocrClientConfig ?? []);
 window.ANTRIAN_OCR_MESSAGES = {
 extractFail: {
 problem: 'Gagal Melakukan Ekstrak data',
 solution: 'Pastikan yang anda upload adalah KTP atau perbaiki cara pengambilan gambar anda agar lebih baik lagi.'
 }
 };

 window.isOcrExtractError = function(msg) {
 if (!msg) return false;
 var t = String(msg).toLowerCase();
 return /ocr\.?space|easyocr|ocr gagal|tidak menemukan teks|ekstrak data|extract|service ocr|fallback|ocr online|membaca ktp|ktp_image/i.test(t)
 || (t.indexOf('ocr') !== -1 && (t.indexOf('gagal') !== -1 || t.indexOf('fallback') !== -1 || t.indexOf('tidak') !== -1));
 };

 window.normalizeOcrExtractError = function(msg) {
 if (window.isOcrExtractError(msg)) {
 return window.ANTRIAN_OCR_MESSAGES.extractFail;
 }
 return { problem: msg, solution: null };
 };
</script>

{{-- Search Antrian Functions - didefinisikan sebelum antrian-ocr.js agar selalu tersedia --}}
<script>
 // Toast helpers — success/error only, format konsisten dengan sweetalert-disdukcapil.js
 function toastSuccess(title, html) {
 if (typeof fireToast === 'function') {
 return fireToast({ type: 'success', icon: 'success', title: title || 'Berhasil', html: html || undefined, timer: 5000 });
 }
 if (window.SwalHelper && SwalHelper.toastSuccess) {
 return html ? SwalHelper.toastSuccess(html, title) : SwalHelper.toastSuccess(title);
 }
 }
 function toastError(problem, solution, title) {
 var norm = (typeof window.normalizeOcrExtractError === 'function')
 ? window.normalizeOcrExtractError(problem)
 : null;
 if (norm && norm.solution && !solution) {
 problem = norm.problem;
 solution = norm.solution;
 }
 if (typeof fireToast === 'function') {
 return fireToast({
 type: 'error', icon: 'error',
 title: title || 'Terjadi kesalahan',
 problem: problem || 'Terjadi kesalahan saat memproses permintaan.',
 solution: solution || 'Periksa data atau aksi yang sedang dilakukan, lalu coba lagi.',
 timer: 5000
 });
 }
 if (window.SwalHelper && SwalHelper.toastError) {
 return SwalHelper.toastError(problem, solution);
 }
 }

 // Helper: deteksi format nomor antrian (ABC-123-456 atau ABC123)
 // Hanya true jika: 3 huruf di awal DAN ada angka setelahnya
 window.isQueueNumberFormat = function(input) {
 if (!input || typeof input !== 'string') return false;
 var cleaned = input.replace(/[-\s]/g, '').toUpperCase();
 // Format nomor antrian: 3 huruf + minimal 1 angka
 // Contoh: ABC123, ABC-123-456, ABC1
 var queuePattern = /^[A-Z]{3,}\d+$/;
 // Atau format dengan dash: ABC-123-456
 var dashPattern = /^[A-Z]{3,}-\d+(-\d+)*$/;
 return queuePattern.test(cleaned) || dashPattern.test(input.toUpperCase());
 };

 // Helper: format nomor antrian ke standar ABC-123-456
 window.formatQueueNumber = function(input) {
 if (!input || typeof input !== 'string') return null;
 var cleaned = input.toUpperCase().replace(/[^A-Z0-9]/g, '');
 if (cleaned.length < 3) return null;
 var letters = cleaned.substring(0, 3);
 var numbers = cleaned.substring(3);
 if (numbers.length < 6) {
 numbers = numbers.padEnd(6, '0');
 }
 var part1 = numbers.substring(0, 3);
 var part2 = numbers.substring(3, 6);
 return letters + '-' + part1 + '-' + part2;
 };

 window.normalizeNikInput = function(input) {
 return String(input || '').replace(/\D/g, '');
 };

 window.isNikFormat = function(input) {
 return /^\d{16}$/.test(window.normalizeNikInput(input));
 };

 window.buildLacakSearchParams = function(searchValue) {
 var params = new URLSearchParams();
 var trimmed = String(searchValue || '').trim();
 if (!trimmed) return params;

 if (window.isQueueNumberFormat(trimmed)) {
 var formatted = window.formatQueueNumber ? window.formatQueueNumber(trimmed) : null;
 params.append('nomor_antrian', formatted || trimmed.toUpperCase());
 } else {
 params.append('nik', window.normalizeNikInput(trimmed));
 }
 return params;
 };

 // Fungsi pencarian antrian - global scope
 // ==== Auto-refresh status (polling) ====
 window.__lacakPollState = window.__lacakPollState || { interval: null, lastSearch: '', lastLayanan: '', lastStatuses: {}, isPernikahanOnly: false };

 window.stopLacakPolling = function() {
 if (window.__lacakPollState.interval) {
 clearInterval(window.__lacakPollState.interval);
 window.__lacakPollState.interval = null;
 console.log('[Lacak Polling] Stopped');
 }
 };

 window.startLacakPolling = function(searchValue, layananId, isPernikahanOnly) {
 window.stopLacakPolling();
 window.__lacakPollState.lastSearch = searchValue;
 window.__lacakPollState.lastLayanan = layananId || '';
 window.__lacakPollState.isPernikahanOnly = !!isPernikahanOnly;
 console.log('[Lacak Polling] Started for', searchValue, isPernikahanOnly ? '(pernikahan)' : '');
 window.__lacakPollState.interval = setInterval(window.pollLacakStatus, 10000);
 };

 window.pollLacakStatus = function() {
 var s = window.__lacakPollState.lastSearch;
 if (!s) { window.stopLacakPolling(); return; }
 if (document.hidden) return;

 if (window.__lacakPollState.isPernikahanOnly) {
 var apiUrl = '{{ url('/api/pernikahan/status/') }}' + encodeURIComponent(s);
 fetch(apiUrl, {
 method: 'GET',
 headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(function(r) { return r.ok ? r.json() : null; })
 .then(function(data) {
 if (!data || !data.success || !data.data) return;
 var container = document.getElementById('searchResults');
 if (!container) return;
 var item = data.data;
 var key = item.nomor_antrian || item.pernikahan_id;
 var newStatus = item.status || '';
 var oldStatus = window.__lacakPollState.lastStatuses[key];
 if (oldStatus && newStatus && oldStatus !== newStatus) {
 toastSuccess('Status Diperbarui', key + ': ' + (item.status_label || newStatus));
 }
 if (newStatus) window.__lacakPollState.lastStatuses[key] = newStatus;
 if (window.renderPernikahanResult) {
 window.renderPernikahanResult([item], container);
 }
 })
 .catch(function(e) { console.warn('[Lacak Polling] pernikahan error', e); });
 return;
 }

 var params = window.buildLacakSearchParams(s);
 if (window.__lacakPollState.lastLayanan) {
 params.append('layanan_id', window.__lacakPollState.lastLayanan);
 }

 fetch('{{ route('antrian.search') }}?' + params.toString(), {
 method: 'GET',
 headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(function(r) { return r.ok ? r.json() : null; })
 .then(function(data) {
 if (!data || !data.success || !data.data || !data.data.length) return;
 var container = document.getElementById('searchResults');
 if (!container) return;

 // Detect status changes for toast notification
 var changed = false;
 data.data.forEach(function(item) {
 var key = item.nomor_antrian || item.antrian_online_id;
 var newStatus = (item.pernikahan && item.pernikahan.status)
 ? item.pernikahan.status
 : (item.status_antrian || item.status);
 var oldStatus = window.__lacakPollState.lastStatuses[key];
 if (oldStatus && newStatus && oldStatus !== newStatus) {
 var label = (item.pernikahan && item.pernikahan.status_label) ? item.pernikahan.status_label : newStatus;
 toastSuccess('Status Diperbarui', key + ': ' + label);
 changed = true;
 }
 if (newStatus) window.__lacakPollState.lastStatuses[key] = newStatus;
 });

 if (window.renderSearchResults) {
 window.renderSearchResults(data.data);
 }
 })
 .catch(function(e) { console.warn('[Lacak Polling] error', e); });
 };

 // Stop polling when page unloads
 window.addEventListener('beforeunload', function() { window.stopLacakPolling(); });

 window.searchAntrian = function() {
 try {
 console.log('=== SEARCH ANTRIAN DIPANGGIL ===');

 var searchInput = document.getElementById('searchInput');
 var searchLayanan = document.getElementById('searchLayanan');
 var resultsContainer = document.getElementById('searchResults');

 if (!searchInput) {
 console.error('searchInput element not found');
 toastError('Elemen input pencarian tidak ditemukan.', 'Muat ulang halaman, lalu coba cari lagi.');
 return;
 }

 var searchValue = searchInput.value.trim();
 var layananId = searchLayanan ? searchLayanan.value : '';

 if (window.PagesFormGuard) {
 if (!window.PagesFormGuard.validateField(searchInput)) return;
 if (searchLayanan && !window.PagesFormGuard.validateField(searchLayanan)) return;
 }

 console.log('Search value:', searchValue);
 console.log('Layanan ID:', layananId);

 if (!searchValue) {
 toastError('Kata kunci pencarian kosong.', 'Masukkan NIK (16 digit) atau nomor antrian, lalu tekan tombol Cari.');
 return;
 }

 var isQueueNumber = window.isQueueNumberFormat(searchValue);
 if (!isQueueNumber && !window.isNikFormat(searchValue)) {
 toastError(
 'Format pencarian tidak valid.',
 'Masukkan NIK 16 digit angka atau nomor antrian (contoh: ABC-123-456).'
 );
 if (resultsContainer) {
 resultsContainer.innerHTML = '';
 }
 return;
 }

 // Tampilkan loading di results container
 if (resultsContainer) {
 resultsContainer.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-green-500 border-t-transparent mb-4"></div><p class="text-gray-500 font-medium">Mencari data antrian...</p></div>';
 }

 // Deteksi nomor antrian pernikahan (PNK-) atau coba antrian umum dulu
 var isPernikahanPrefix = searchValue.toUpperCase().startsWith('PNK-');

 if (isPernikahanPrefix) {
 window.searchPernikahan(searchValue, resultsContainer);
 return;
 }

 // Build query params untuk antrian (termasuk layanan pernikahan via nomor antrian umum)
 var params = window.buildLacakSearchParams(searchValue);

 if (layananId) {
 params.append('layanan_id', layananId);
 }

 console.log('Searching with params:', params.toString());

 var searchUrl = '{{ route('antrian.search') }}?' + params.toString();
 console.log('Search URL:', searchUrl);

 fetch(searchUrl, {
 method: 'GET',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 }
 })
 .then(function(response) {
 console.log('Response status:', response.status);
 if (!response.ok) {
 throw new Error('HTTP error! status: ' + response.status);
 }
 return response.json();
 })
 .then(function(data) {
 console.log('Search response:', data);
 console.log('Response success:', data.success);
 console.log('Response data:', data.data);
 console.log('Response data length:', data.data ? data.data.length : 0);

 if (!resultsContainer) {
 console.error('resultsContainer not found');
 return;
 }

 if (data.success && data.data && data.data.length > 0) {
 console.log('Rendering ' + data.data.length + ' results');
 window.renderSearchResults(data.data);

 // Seed status cache & start auto-refresh polling
 window.__lacakPollState.lastStatuses = {};
 data.data.forEach(function(item) {
 var key = item.nomor_antrian || item.antrian_online_id;
 var st = (item.pernikahan && item.pernikahan.status)
 ? item.pernikahan.status
 : (item.status_antrian || item.status);
 if (key && st) window.__lacakPollState.lastStatuses[key] = st;
 });
 window.startLacakPolling(searchValue, layananId, false);

 // Notifikasi cari berhasil
 toastSuccess('Ditemukan!', data.data.length + ' data ditemukan untuk "' + searchValue + '"');
 } else {
 console.log('No results found. Message:', data.message || 'No message');
 // Fallback pernikahan hanya untuk nomor antrian (bukan NIK)
 if (window.isQueueNumberFormat(searchValue)) {
 window.searchPernikahan(searchValue, resultsContainer, true);
 return;
 }
 window.stopLacakPolling();
 var debugInfo = data.debug ? '<br><small class="text-gray-400">Debug: Mencari ' + data.debug.search_type + ' = ' + (data.debug.params.nik || data.debug.params.nomor_antrian || data.debug.params.search || 'kosong') + '</small>' : '';
 resultsContainer.innerHTML = '<div class="text-center py-8 animate-fade-in"><div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-search text-3xl text-gray-400"></i></div><p class="text-gray-500 font-medium">Data antrian tidak ditemukan.</p><p class="text-sm text-gray-400 mt-1">Pastikan NIK atau nomor antrian yang dimasukkan benar.</p><div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg inline-block"><p class="text-sm text-yellow-700"><i class="fas fa-lightbulb mr-1"></i><strong>Tips:</strong> Gunakan NIK 16 digit sesuai KTP atau nomor antrian yang Anda terima saat pendaftaran.</p></div>' + debugInfo + '</div>';
 toastError(
 'Data untuk "' + searchValue + '" tidak ditemukan dalam sistem.',
 'Pastikan NIK (16 digit) atau nomor antrian yang dimasukkan benar.'
 );
 }
 })
 .catch(function(err) {
 console.error('Search Error:', err);
 if (resultsContainer) {
 resultsContainer.innerHTML = '<div class="text-center py-8"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-exclamation-triangle text-3xl text-red-500"></i></div><p class="text-gray-500 font-medium">Gagal mencari data.</p><p class="text-sm text-gray-400 mt-1">' + (err.message || 'Terjadi kesalahan koneksi') + '</p></div>';
 }
 // Gunakan notifikasi error
 toastError(
 'Gagal mencari data: ' + (err.message || 'Terjadi kesalahan koneksi'),
 'Periksa koneksi internet, lalu coba lagi.'
 );
 });
 } catch (err) {
 console.error('Unexpected error in searchAntrian:', err);
 toastError('Terjadi kesalahan: ' + err.message, 'Muat ulang halaman, lalu coba lagi.');
 }
 };

 // Fungsi pencarian pernikahan (isFallback = true jika dipanggil setelah antrian.search kosong)
 window.searchPernikahan = function(nomorAntrian, container, isFallback) {
 var apiUrl = '{{ url('/api/pernikahan/status/') }}' + encodeURIComponent(nomorAntrian);

 fetch(apiUrl, {
 method: 'GET',
 headers: {
 'Accept': 'application/json',
 'X-Requested-With': 'XMLHttpRequest'
 }
 })
 .then(function(response) {
 if (!response.ok) {
 if (response.status === 404) {
 return { success: false, message: 'Nomor antrian pernikahan tidak ditemukan' };
 }
 throw new Error('HTTP error! status: ' + response.status);
 }
 return response.json();
 })
 .then(function(data) {
 if (data.success && data.data) {
 window.renderPernikahanResult([data.data], container);
 window.__lacakPollState.lastStatuses = {};
 var key = data.data.nomor_antrian || data.data.pernikahan_id;
 if (key && data.data.status) {
 window.__lacakPollState.lastStatuses[key] = data.data.status;
 }
 window.startLacakPolling(nomorAntrian, '', true);
 toastSuccess('Ditemukan!', 'Data pernikahan ditemukan untuk "' + nomorAntrian + '"');
 } else {
 if (isFallback) {
 window.stopLacakPolling();
 container.innerHTML = '<div class="text-center py-8 animate-fade-in"><div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-search text-3xl text-gray-400"></i></div><p class="text-gray-500 font-medium">Data antrian tidak ditemukan.</p><p class="text-sm text-gray-400 mt-1">Pastikan NIK atau nomor antrian yang dimasukkan benar.</p></div>';
 toastError(
 'Data untuk "' + nomorAntrian + '" tidak ditemukan dalam sistem.',
 'Pastikan NIK (16 digit) atau nomor antrian yang dimasukkan benar.'
 );
 } else {
 container.innerHTML = '<div class="text-center py-8 animate-fade-in"><div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-search text-3xl text-gray-400"></i></div><p class="text-gray-500 font-medium">Data pernikahan tidak ditemukan.</p><p class="text-sm text-gray-400 mt-1">Pastikan nomor antrian pernikahan yang dimasukkan benar (format: PNK-XXXXXXXX).</p></div>';
 toastError(
 'Data pernikahan untuk "' + nomorAntrian + '" tidak ditemukan.',
 'Pastikan nomor antrian pernikahan benar (format: PNK-XXXXXXXX).'
 );
 }
 }
 })
 .catch(function(err) {
 console.error('Search Pernikahan Error:', err);
 container.innerHTML = '<div class="text-center py-8"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-exclamation-triangle text-3xl text-red-500"></i></div><p class="text-gray-500 font-medium">Gagal mencari data pernikahan.</p><p class="text-sm text-gray-400 mt-1">' + (err.message || 'Terjadi kesalahan koneksi') + '</p></div>';
 toastError(
 'Gagal mencari data pernikahan: ' + (err.message || 'Terjadi kesalahan koneksi'),
 'Periksa koneksi internet, lalu coba lagi.'
 );
 });
 };

 // Render hasil pencarian pernikahan
 window.renderPernikahanResult = function(results, container) {
 var statusPernikahanConfig = {
 'MENUNGGU_KONFIRMASI_KEAGAMAAN': { bg: 'bg-yellow-100', text: 'text-yellow-700', border: 'border-yellow-200', hex: '#f59e0b', label: 'Menunggu Konfirmasi Keagamaan', icon: 'fa-clock' },
 'DITOLAK_KEAGAMAAN': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', hex: '#ef4444', label: 'Ditolak', icon: 'fa-times-circle' },
 'MENUNGGU_APPROVE_TANGGAL': { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200', hex: '#3b82f6', label: 'Menunggu Persetujuan Tanggal', icon: 'fa-calendar-check' },
 'TANGGAL_DITOLAK': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Tanggal Ditolak', icon: 'fa-calendar-times' },
 'TANGGAL_DISETUJUI': { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200', hex: '#22c55e', label: 'Tanggal Disetujui', icon: 'fa-check-circle' },
 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI': { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-200', hex: '#a855f7', label: 'Menunggu Verifikasi Dokumen', icon: 'fa-file-search' },
 'DOKUMEN_PERLU_PERBAIKAN': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Dokumen Perlu Perbaikan', icon: 'fa-exclamation-triangle' },
 'DOKUMEN_DIVERIFIKASI': { bg: 'bg-teal-100', text: 'text-teal-700', border: 'border-teal-200', hex: '#14b8a6', label: 'Dokumen Diverifikasi', icon: 'fa-file-check' },
 'SELESAI': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', hex: '#10b981', label: 'Selesai', icon: 'fa-check-double' }
 };

 var html = results.map(function(item) {
 var statusConfig = statusPernikahanConfig[item.status] || statusPernikahanConfig['MENUNGGU_KONFIRMASI_KEAGAMAAN'];
 var nomorAntrian = item.nomor_antrian || '-';
 var namaPemohon = item.nama_pemohon || '-';
 var statusLabel = statusConfig.label;
 var statusIcon = statusConfig.icon;

 // Progress bar untuk step
 var progressInfo = window.resolvePernikahanProgress(item, null);
 var stepWidth = progressInfo.stepWidth;
 var progressHtml = '<div class="mt-3">' +
 '<div class="flex justify-between text-xs text-gray-500 mb-1">' +
 '<span>Progress</span>' +
 '<span>' + progressInfo.progressLabel + '</span>' +
 '</div>' +
 '<div class="w-full bg-gray-200 rounded-full h-2">' +
 '<div class="bg-gradient-to-r ' + progressInfo.progressGradient + ' h-2 rounded-full transition-all duration-500" style="width: ' + stepWidth + '%"></div>' +
 '</div>' +
 (progressInfo.progressSubtitle ? '<p class="text-xs text-red-700 mt-1 font-medium">' + progressInfo.progressSubtitle + '</p>' : '') +
 '</div>';

 var timelineHtml = '';
 if (progressInfo.lacakSorted && progressInfo.lacakSorted.length > 0) {
 timelineHtml = window.buildLacakTimelineHtml(progressInfo.lacakSorted, window.PERNIKAHAN_LACAK_COLORS);
 }

 return '<div class="search-result-card bg-white border-2 ' + statusConfig.border + ' rounded-xl p-5 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer" style="animation-delay: 0s" onclick="window.showPernikahanDetail(' + JSON.stringify(item).replace(/"/g, '&quot;') + ')">' +
 '<div class="flex items-center gap-2 mb-3">' +
 '<div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white shadow-lg">' +
 '<i class="fas fa-ring text-xl"></i>' +
 '</div>' +
 '<div class="flex-1">' +
 '<div class="flex items-center gap-2 mb-1">' +
 '<span class="text-xs font-semibold text-purple-600 bg-purple-100 px-2 py-0.5 rounded">PERNIKAHAN</span>' +
 '</div>' +
 '<h3 class="font-bold text-xl text-purple-600">' + nomorAntrian + '</h3>' +
 '<p class="text-gray-800 font-semibold">' + namaPemohon + '</p>' +
 '</div>' +
 '<div class="flex items-center gap-2 px-3 py-2 rounded-full ' + statusConfig.bg + ' ' + statusConfig.text + ' border ' + statusConfig.border + ' font-bold text-xs shadow-sm">' +
 '<i class="fas ' + statusIcon + '"></i>' +
 '<span>' + statusLabel + '</span>' +
 '</div>' +
 '</div>' +
 progressHtml +
 '<div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs text-gray-600">' +
 '<div><i class="fas fa-calendar mr-1 text-purple-500"></i>' + (item.tanggal_perkawinan || '-') + '</div>' +
 '<div><i class="fas fa-church mr-1 text-purple-500"></i>' + (item.nama_gereja || '-') + '</div>' +
 '</div>' +
 timelineHtml +
 '</div>';
 }).join('');

 container.innerHTML = html;
 };

 // Tampilkan detail pernikahan
 window.showPernikahanDetail = function(pernikahan) {
 var statusPernikahanConfig = {
 'MENUNGGU_KONFIRMASI_KEAGAMAAN': { hex: '#f59e0b', label: 'Menunggu Konfirmasi Keagamaan', icon: 'fa-clock' },
 'DITOLAK_KEAGAMAAN': { hex: '#ef4444', label: 'Ditolak', icon: 'fa-times-circle' },
 'MENUNGGU_APPROVE_TANGGAL': { hex: '#3b82f6', label: 'Menunggu Persetujuan Tanggal', icon: 'fa-calendar-check' },
 'TANGGAL_DITOLAK': { hex: '#f97316', label: 'Tanggal Ditolak', icon: 'fa-calendar-times' },
 'TANGGAL_DISETUJUI': { hex: '#22c55e', label: 'Tanggal Disetujui', icon: 'fa-check-circle' },
 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI': { hex: '#a855f7', label: 'Menunggu Verifikasi Dokumen', icon: 'fa-file-search' },
 'DOKUMEN_PERLU_PERBAIKAN': { hex: '#f97316', label: 'Dokumen Perlu Perbaikan', icon: 'fa-exclamation-triangle' },
 'DOKUMEN_DIVERIFIKASI': { hex: '#14b8a6', label: 'Dokumen Diverifikasi', icon: 'fa-file-check' },
 'SELESAI': { hex: '#10b981', label: 'Selesai', icon: 'fa-check-double' }
 };

 var statusConfig = statusPernikahanConfig[pernikahan.status] || statusPernikahanConfig['MENUNGGU_KONFIRMASI_KEAGAMAAN'];
 var progressInfo = window.resolvePernikahanProgress(pernikahan, null);
 var stepWidth = progressInfo.stepWidth;

 var modalContent = `
 <div class="p-6">
 <div class="flex items-center justify-between mb-4">
 <div class="flex items-center gap-3">
 <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-white">
 <i class="fas fa-ring text-xl"></i>
 </div>
 <div>
 <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-2 py-0.5 rounded">PERNIKAHAN</span>
 <h3 class="font-bold text-xl text-gray-800">${pernikahan.nomor_antrian}</h3>
 </div>
 </div>
 <button onclick="Swal.close()" class="text-gray-400 hover:text-gray-600">
 <i class="fas fa-times text-xl"></i>
 </button>
 </div>

 <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 mb-4">
 <div class="flex items-center gap-3">
 <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-2xl" style="background-color: ${statusConfig.hex}">
 <i class="fas ${statusConfig.icon}"></i>
 </div>
 <div>
 <p class="font-bold text-lg" style="color: ${statusConfig.hex}">${statusConfig.label}</p>
 <p class="text-xs text-gray-500">Status saat ini</p>
 </div>
 </div>
 </div>

 <div class="mb-4">
 <div class="flex justify-between text-xs text-gray-500 mb-1">
 <span>Progress Pengajuan</span>
 <span>Step ${progressInfo.currentStep} dari ${progressInfo.totalSteps}</span>
 </div>
 <div class="w-full bg-gray-200 rounded-full h-3">
 <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all" style="width: ${stepWidth}%"></div>
 </div>
 </div>

 <div class="bg-gray-50 rounded-xl p-4 space-y-3 mb-4">
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Nama Pemohon</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_pemohon || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Mempelai Pria</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_mempelai_pria || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Mempelai Wanita</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_mempelai_wanita || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Tanggal Perkawinan</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.tanggal_perkawinan || '-'}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-xs text-gray-500">Gereja/Lembaga</span>
 <span class="font-semibold text-gray-800 text-sm">${pernikahan.nama_gereja || '-'}</span>
 </div>
 </div>

 ${pernikahan.catatan_keagamaan || pernikahan.catatan_admin || pernikahan.alasan_ditolak ? `
 <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
 <p class="text-xs font-semibold text-yellow-800 mb-2"><i class="fas fa-info-circle mr-1"></i>Catatan</p>
 ${pernikahan.catatan_keagamaan ? '<p class="text-xs text-gray-700 mb-1"><strong>Keagamaan:</strong> ' + pernikahan.catatan_keagamaan + '</p>' : ''}
 ${pernikahan.catatan_admin ? '<p class="text-xs text-gray-700 mb-1"><strong>Admin:</strong> ' + pernikahan.catatan_admin + '</p>' : ''}
 ${pernikahan.alasan_ditolak ? '<p class="text-xs text-red-700"><strong>Alasan Ditolak:</strong> ' + pernikahan.alasan_ditolak + '</p>' : ''}
 </div>
 ` : ''}

 <div class="flex gap-2">
 <button onclick="window.copyNomorAntrianToClipboard('${pernikahan.nomor_antrian}'); Swal.close();" class="flex-1 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700">
 <i class="fas fa-copy mr-1"></i> Salin Nomor
 </button>
 <button onclick="Swal.close()" class="flex-1 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-300">
 Tutup
 </button>
 </div>
 </div>
 `;

 Swal.fire({
 html: modalContent,
 showConfirmButton: false,
 customClass: {
 popup: 'rounded-2xl'
 }
 });
 };

 // Render Search Results
 window.__antrianRegistry = window.__antrianRegistry || {};

 // --- Progress & riwayat antrian (5 tahap: KK, akta lahir/mati/kematian) ---
 window.ANTRIAN_TOTAL_STEPS = 5;

 // Tahap penolakan: Verifikasi Data (btnTolak di admin penerbitan_*_detail)
 window.ANTRIAN_REJECTION_STEP = 3;
 window.ANTRIAN_REJECTION_TOTAL_STEPS = 5;
 window.ANTRIAN_REJECTION_STATUS = 'Verifikasi Data';

 window.ANTRIAN_STEP_MAP = {
 'Menunggu': 1,
 'Dokumen Diterima': 2,
 'Verifikasi Data': 3,
 'Proses Cetak': 4,
 'Selesai': 5,
 // Legacy (data lama — dilewati atau dipetakan ke tahap terdekat)
 'Siap Pengambilan': null,
 'Berkas Siap Diunduh': 5,
 'Tolak': null,
 'Ditolak': null,
 'Dibatalkan': null
 };

 window.ANTRIAN_CANONICAL_STEPS = [
 { step: 1, status: 'Menunggu', keterangan: 'Antrian berhasil dibuat. Menunggu dokumen diterima oleh admin.' },
 { step: 2, status: 'Dokumen Diterima', keterangan: 'Dokumen diterima oleh admin. Menunggu verifikasi data.' },
 { step: 3, status: 'Verifikasi Data', keterangan: 'Data sedang diverifikasi oleh petugas.' },
 { step: 4, status: 'Proses Cetak', keterangan: 'Dokumen sedang dalam proses cetak.' },
 { step: 5, status: 'Selesai', keterangan: 'Permohonan selesai diproses.' }
 ];

 window.ANTRIAN_REJECTION_LACAK_STATUSES = ['Tolak', 'Ditolak', 'Dibatalkan'];

 window.isAntrianRejectionLacakStatus = function(status) {
 return window.ANTRIAN_REJECTION_LACAK_STATUSES.indexOf(status) >= 0;
 };

 /**
 * Ambil alasan penolakan dari antrian atau riwayat lacak (status Tolak/Ditolak).
 * Cari di berbagai format keterangan untuk ekstrak alasan dengan robust.
 */
 window.extractAlasanPenolakan = function(antrian) {
 if (!antrian) return null;

 if (antrian.alasan_penolakan && String(antrian.alasan_penolakan).trim() !== '') {
 return String(antrian.alasan_penolakan).trim();
 }

 var lacakItems = Array.isArray(antrian.lacak_berkas) ? antrian.lacak_berkas : [];
 var rejectionRecords = lacakItems.filter(function(lb) {
 return lb && window.isAntrianRejectionLacakStatus(lb.status);
 });
 if (rejectionRecords.length === 0) return null;

 rejectionRecords.sort(function(a, b) {
 var da = new Date(a.created_at || a.tanggal || 0).getTime();
 var db = new Date(b.created_at || b.tanggal || 0).getTime();
 return db - da;
 });

 var latest = rejectionRecords[0];
 if (latest.alasan_penolakan && String(latest.alasan_penolakan).trim() !== '') {
 return String(latest.alasan_penolakan).trim();
 }

 if (latest.keterangan && String(latest.keterangan).trim() !== '') {
 var ket = String(latest.keterangan).trim();
 var patterns = [
 /Alasan:\s*(.+?)(?:\.|$)/i,
 /berkas\s+(.+?)(?:\.|$)/i,
 /(.+?)(?:Alasan|berkas)/i,
 /:\s*(.+?)$/i
 ];
 for (var i = 0; i < patterns.length; i++) {
 var match = ket.match(patterns[i]);
 if (match && match[1]) {
 var extracted = String(match[1]).trim();
 if (extracted.length > 0 && extracted.length < 200) {
 return extracted;
 }
 }
 }
 return ket;
 }

 return null;
 };

 /**
 * Tahap penolakan: Verifikasi Data (step 3 dalam workflow 5-tahap).
 */
 window.resolveAntrianRejectionMilestone = function() {
 return {
 failedAtStatus: window.ANTRIAN_REJECTION_STATUS,
 failedAtStep: window.ANTRIAN_REJECTION_STEP,
 totalSteps: window.ANTRIAN_REJECTION_TOTAL_STEPS
 };
 };

 /**
 * Riwayat ditolak: 2 tahap pipeline (Menunggu, Verifikasi Data) + entri penolakan (Tolak/Ditolak).
 */
 window.buildAntrianRejectedTimeline = function(lacakSorted, antrian) {
 var pipeline = window.normalizeAntrianLacakHistory(
 lacakSorted,
 window.ANTRIAN_REJECTION_STEP,
 antrian
 );
 var rejection = null;
 (lacakSorted || []).forEach(function(lb) {
 if (lb && window.isAntrianRejectionLacakStatus(lb.status)) {
 rejection = lb;
 }
 });
 if (rejection) {
 pipeline.push(rejection);
 }
 return pipeline;
 };

 window.PERNIKAHAN_STEP_MAP = {
 'Menunggu': 1,
 'MENUNGGU_KONFIRMASI_KEAGAMAAN': 2,
 'DITOLAK_KEAGAMAAN': 2,
 'MENUNGGU_APPROVE_TANGGAL': 3,
 'TANGGAL_DITOLAK': 3,
 'TANGGAL_DISETUJUI': 4,
 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI': 5,
 'DOKUMEN_PERLU_PERBAIKAN': 5,
 'DOKUMEN_DIVERIFIKASI': 6,
 'SELESAI': 7
 };

 window.PERNIKAHAN_LACAK_COLORS = {
 'Konfirmasi Keagamaan': { hex: '#f59e0b' },
 'Ditolak': { hex: '#ef4444' },
 'Persetujuan Tanggal': { hex: '#3b82f6' },
 'Tanggal Ditolak': { hex: '#f97316' },
 'Tanggal Disetujui': { hex: '#22c55e' },
 'Verifikasi Dokumen': { hex: '#a855f7' },
 'Dokumen Perlu Perbaikan': { hex: '#f97316' },
 'Dokumen Diverifikasi': { hex: '#14b8a6' },
 'Selesai': { hex: '#10b981' }
 };

 window.sortLacakBerkasChronological = function(items) {
 if (!Array.isArray(items) || items.length === 0) return [];
 return items.slice().sort(function(a, b) {
 var da = new Date(a.created_at || a.tanggal || 0).getTime();
 var db = new Date(b.created_at || b.tanggal || 0).getTime();
 return da - db;
 });
 };

 window.formatLacakDate = function(lb) {
 var raw = lb.tanggal || lb.created_at;
 if (!raw) return '-';
 try {
 return new Date(raw).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
 } catch (e) {
 return String(raw);
 }
 };

 /**
 * Petakan status lacak_berkas ke nomor step (1–5).
 */
 window.getAntrianLacakStepNumber = function(status) {
 if (!status) return null;
 return Object.prototype.hasOwnProperty.call(window.ANTRIAN_STEP_MAP, status)
 ? window.ANTRIAN_STEP_MAP[status]
 : null;
 };

 /**
 * Lengkapi riwayat lacak agar jumlah entri = progress step (maks. 5).
 * Diperbaiki untuk memastikan semua step 1-stepLimit diisi (synthetic jika perlu).
 */
 window.normalizeAntrianLacakHistory = function(lacakSorted, targetStep, antrian) {
 var totalSteps = window.ANTRIAN_TOTAL_STEPS;
 var stepLimit = Math.min(Math.max(targetStep || 1, 1), totalSteps);

 var recordsByStep = {};
 if (Array.isArray(lacakSorted) && lacakSorted.length > 0) {
 lacakSorted.forEach(function(lb) {
 var step = window.getAntrianLacakStepNumber(lb.status);
 if (step == null || step < 1 || step > totalSteps) return;
 var existing = recordsByStep[step];
 if (!existing) {
 recordsByStep[step] = lb;
 return;
 }
 if (step === 5 && (lb.download_url || lb.file_berkas) && !(existing.download_url || existing.file_berkas)) {
 recordsByStep[step] = lb;
 }
 });
 }

 var pickSyntheticDate = function(step) {
 var nextDate = null;
 for (var s = step + 1; s <= stepLimit; s++) {
 if (recordsByStep[s]) {
 nextDate = recordsByStep[s].tanggal || recordsByStep[s].created_at;
 break;
 }
 }
 if (nextDate) return nextDate;
 for (var p = step - 1; p >= 1; p--) {
 if (recordsByStep[p]) {
 return recordsByStep[p].tanggal || recordsByStep[p].created_at;
 }
 }
 if (antrian && antrian.created_at) return antrian.created_at;
 if (Array.isArray(lacakSorted) && lacakSorted.length > 0) {
 return lacakSorted[0].tanggal || lacakSorted[0].created_at || null;
 }
 return new Date().toISOString();
 };

 var normalized = [];
 for (var s = 1; s <= stepLimit; s++) {
 var canonical = window.ANTRIAN_CANONICAL_STEPS[s - 1];
 if (!canonical) continue;

 var existing = recordsByStep[s];
 if (existing) {
 normalized.push(existing);
 } else {
 normalized.push({
 status: canonical.status,
 keterangan: canonical.keterangan,
 tanggal: pickSyntheticDate(s),
 created_at: pickSyntheticDate(s),
 synthetic: true
 });
 }
 }

 return normalized;
 };

 /**
 * Hitung progress dari riwayat lacak_berkas (5 tahap normal).
 * Ditolak: tahap Verifikasi Data (step 3) + riwayat penolakan.
 */
 window.resolveAntrianProgress = function(antrian) {
 var status = antrian.status_antrian || 'Menunggu';
 var isDitolak = status === 'Ditolak';
 var isDibatalkan = status === 'Dibatalkan';
 var lacakSorted = window.sortLacakBerkasChronological(antrian.lacak_berkas || []);
 var totalSteps = window.ANTRIAN_TOTAL_STEPS;

 if (isDitolak || isDibatalkan) {
 var rejectionMilestone = isDitolak
 ? window.resolveAntrianRejectionMilestone()
 : { failedAtStatus: 'Menunggu', failedAtStep: 1, totalSteps: totalSteps };
 var failedAtStatus = rejectionMilestone.failedAtStatus;
 var failedAtStep = rejectionMilestone.failedAtStep;
 var rejectionTotalSteps = rejectionMilestone.totalSteps || window.ANTRIAN_REJECTION_TOTAL_STEPS;
 var progressSubtitle = isDitolak
 ? 'Ditolak pada tahap <strong>' + failedAtStatus + '</strong> (Step ' + failedAtStep + ' dari ' + rejectionTotalSteps + ')'
 : 'Dibatalkan pada tahap <strong>' + failedAtStatus + '</strong> (Step ' + failedAtStep + ' dari ' + rejectionTotalSteps + ')';

 return {
 isDitolak: isDitolak,
 isDibatalkan: isDibatalkan,
 isTerminal: true,
 currentStep: failedAtStep,
 totalSteps: rejectionTotalSteps,
 stepWidth: Math.round((failedAtStep / rejectionTotalSteps) * 100),
 failedAtStatus: failedAtStatus,
 failedAtStep: failedAtStep,
 progressLabel: 'Step ' + failedAtStep + ' dari ' + rejectionTotalSteps,
 progressSubtitle: progressSubtitle,
 lacakSorted: isDitolak
 ? window.buildAntrianRejectedTimeline(lacakSorted, antrian)
 : lacakSorted
 };
 }

 var currentStep = window.ANTRIAN_STEP_MAP[status] || 1;
 if (status === 'Selesai' || status === 'Berkas Siap Diunduh') {
 currentStep = 5;
 } else if (status === 'Siap Pengambilan') {
 currentStep = 4;
 }
 return {
 isDitolak: false,
 isDibatalkan: false,
 isTerminal: status === 'Selesai' || status === 'Berkas Siap Diunduh',
 currentStep: currentStep,
 totalSteps: totalSteps,
 stepWidth: Math.round((currentStep / totalSteps) * 100),
 failedAtStatus: null,
 failedAtStep: null,
 progressLabel: 'Step ' + currentStep + ' dari ' + totalSteps,
 progressSubtitle: null,
 lacakSorted: window.normalizeAntrianLacakHistory(lacakSorted, currentStep, antrian)
 };
 };

 /**
 * Hitung progress khusus layanan pernikahan dari status + riwayat lacak_berkas.
 * Workflow pernikahan: 7 tahap (align dengan riwayat status yang ditampilkan).
 */
 window.resolvePernikahanProgress = function(pernikahan, antrian) {
 var totalSteps = 7;
 var status = pernikahan.status || 'MENUNGGU_KONFIRMASI_KEAGAMAAN';
 var isDitolak = status === 'DITOLAK_KEAGAMAAN' || status === 'TANGGAL_DITOLAK';
 var lacakSorted = window.sortLacakBerkasChronological(
 (pernikahan.riwayat && pernikahan.riwayat.length)
 ? pernikahan.riwayat
 : ((antrian && antrian.lacak_berkas) ? antrian.lacak_berkas : [])
 );

 if (isDitolak) {
 var failedAtStep = window.PERNIKAHAN_STEP_MAP[status] || 2;
 return {
 isDitolak: true,
 isTerminal: true,
 currentStep: failedAtStep,
 totalSteps: totalSteps,
 stepWidth: Math.round((failedAtStep / totalSteps) * 100),
 progressLabel: 'Step ' + failedAtStep + ' dari ' + totalSteps,
 progressSubtitle: 'Permohonan ditolak pada tahap ' + failedAtStep,
 lacakSorted: lacakSorted,
 progressGradient: 'from-red-500 to-rose-600'
 };
 }

 var currentStep = window.PERNIKAHAN_STEP_MAP[status] || 2;
 return {
 isDitolak: false,
 isTerminal: status === 'SELESAI',
 currentStep: currentStep,
 totalSteps: totalSteps,
 stepWidth: Math.round((currentStep / totalSteps) * 100),
 progressLabel: 'Step ' + currentStep + ' dari ' + totalSteps,
 progressSubtitle: null,
 lacakSorted: lacakSorted,
 progressGradient: 'from-purple-500 to-pink-500'
 };
 };

 window.buildLacakTimelineHtml = function(lacakSorted, statusColors) {
 if (!lacakSorted || lacakSorted.length === 0) return '';

 var items = lacakSorted.map(function(lb, idx) {
 var st = lb.status || '-';
 var isReject = st === 'Ditolak' || st === 'Tolak';
 var isCancel = st === 'Dibatalkan';
 var displayStatus = isReject ? 'Ditolak' : st;
 if (displayStatus === 'Siap Pengambilan') displayStatus = 'Proses Cetak';
 if (displayStatus === 'Berkas Siap Diunduh') displayStatus = 'Selesai';
 var cfg = statusColors[st] || statusColors[displayStatus];
 var dotColor = cfg ? cfg.hex : (isReject ? '#ef4444' : (isCancel ? '#f43f5e' : '#6b7280'));
 var tgl = window.formatLacakDate(lb);
 var isLast = idx === lacakSorted.length - 1;
 var alasanHtml = '';
 if (isReject) {
 var alasanText = '';
 if (lb.alasan_penolakan && String(lb.alasan_penolakan).trim() !== '') {
 alasanText = String(lb.alasan_penolakan).trim();
 } else if (lb.keterangan && String(lb.keterangan).trim() !== '') {
 var alasanMatch = String(lb.keterangan).match(/Alasan:\s*(.+)$/i);
 if (alasanMatch && alasanMatch[1]) {
 alasanText = alasanMatch[1].trim();
 }
 }
 if (alasanText) {
 var alasanEsc = alasanText
 .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
 .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
 alasanHtml = '<p class="text-[11px] text-red-700 mt-0.5 break-words"><i class="fas fa-comment-dots mr-1"></i>' + alasanEsc + '</p>';
 }
 }
 var keteranganHtml = '';
 if (!isReject && lb.keterangan && String(lb.keterangan).trim() !== '') {
 var ketEsc = String(lb.keterangan)
 .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
 keteranganHtml = '<p class="text-[10px] text-gray-500 mt-0.5">' + ketEsc + '</p>';
 }

 // === BARU: tombol download HANYA muncul jika status item ini "Selesai" / "Berkas Siap Diunduh"
 // dan punya download_url (berlaku untuk KK, Akte Lahir, Akte Mati, Lahir Mati) ===
 var downloadHtml = '';
 var isDownloadableStatus = (st === 'Selesai' || st === 'Berkas Siap Diunduh');
 if (lb.download_url && isDownloadableStatus) {
 var dlUrl = lb.download_url + (lb.download_url.indexOf('?') === -1 ? '?' : '&') + 'download=1';
 downloadHtml = '<a href="' + dlUrl + '" onclick="event.stopPropagation()" target="_blank" rel="noopener" ' +
 'class="inline-flex items-center gap-1 mt-1.5 px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-semibold rounded-md shadow-sm transition-colors">' +
 '<i class="fas fa-download"></i> Download Berkas' +
 '</a>';
 }

 return '<li class="flex gap-2 ' + (isLast ? '' : 'pb-2') + '">' +
 '<div class="flex flex-col items-center flex-shrink-0">' +
 '<div class="w-2.5 h-2.5 rounded-full border-2 border-white shadow-sm" style="background-color:' + dotColor + '"></div>' +
 (isLast ? '' : '<div class="w-0.5 flex-1 bg-gray-200 min-h-[12px] mt-0.5"></div>') +
 '</div>' +
 '<div class="flex-1 min-w-0 pb-1">' +
 '<p class="text-xs font-semibold ' + (isReject ? 'text-red-700' : (isCancel ? 'text-rose-700' : 'text-gray-800')) + '">' +
 (isReject ? '<i class="fas fa-ban mr-1"></i>' : (isCancel ? '<i class="fas fa-times mr-1"></i>' : '<i class="fas fa-check-circle mr-1 text-green-600"></i>')) +
 displayStatus +
 '</p>' +
 '<p class="text-[10px] text-gray-400">' + tgl + '</p>' +
 alasanHtml +
 keteranganHtml +
 downloadHtml +
 '</div>' +
 '</li>';
 }).join('');

 return '<div class="mt-4 pt-4 border-t border-gray-100">' +
 '<p class="text-xs font-semibold text-gray-700 mb-2"><i class="fas fa-history mr-1"></i>Riwayat Status (' + lacakSorted.length + ')</p>' +
 '<ol class="list-none m-0 p-0">' + items + '</ol>' +
 '</div>';
 };
 
 // Helper: cari URL dokumen PDF final yang sudah diupload admin
 // Berlaku untuk semua layanan KECUALI pernikahan (pernikahan tetap pakai modal detail)
 window.findDokumenFinalUrl = function(data) {
 if (!data) return null;
 // Skip untuk antrian Ditolak: tidak relevan membuka dokumen, langsung modal detail
 if (data.status_antrian === 'Ditolak') return null;
 // Skip pernikahan: jangan auto-open PDF, biarkan modal detail yang menangani
 if (data.pernikahan && data.pernikahan.status) return null;
 // Cek lacak_berkas: ambil yang punya download_url, prioritaskan paling baru
 if (Array.isArray(data.lacak_berkas) && data.lacak_berkas.length > 0) {
 var withFile = data.lacak_berkas.filter(function(lb) { return lb && lb.download_url; });
 if (withFile.length > 0) {
 withFile.sort(function(a, b) {
 var da = new Date(a.created_at || a.tanggal || 0).getTime();
 var db = new Date(b.created_at || b.tanggal || 0).getTime();
 return db - da;
 });
 return withFile[0].download_url;
 }
 }
 return null;
 };

 window.showAntrianDetailById = function(key) {
 var data = window.__antrianRegistry[key];
 console.log('[Lihat] key=', key, 'data=', data);
 if (!data) { console.warn('Antrian tidak ditemukan di registry:', key, 'available:', Object.keys(window.__antrianRegistry)); return; }

 // Jika sudah ada dokumen PDF final yang diupload admin, buka langsung di tab baru
 var pdfUrl = window.findDokumenFinalUrl(data);
 if (pdfUrl) {
 var viewUrl = pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + 'inline=1';
 console.log('[Lihat] Membuka dokumen final:', viewUrl);
 window.open(viewUrl, '_blank', 'noopener');
 return;
 }

 // Fallback: belum ada dokumen final ? tampilkan modal detail
 if (typeof window.showAntrianDetail === 'function') {
 window.showAntrianDetail(data);
 } else {
 console.error('window.showAntrianDetail tidak terdefinisi');
 }
 };

 // Event delegation: tangani klik tombol Lihat & kartu hasil
 if (!window.__lacakClickBound) {
 window.__lacakClickBound = true;
 document.addEventListener('click', function(e) {
 var btn = e.target.closest('[data-action="lihat-antrian"]');
 if (btn) {
 e.preventDefault();
 e.stopPropagation();
 var key = btn.getAttribute('data-antrian-key');
 window.showAntrianDetailById(key);
 return;
 }
 var card = e.target.closest('[data-card-antrian-key]');
 if (card) {
 var k = card.getAttribute('data-card-antrian-key');
 window.showAntrianDetailById(k);
 }
 });
 }

 window.renderSearchResults = function(results) {
 console.log('Rendering results:', results);
 
 if (!results || results.length === 0) {
 document.getElementById('searchResults').innerHTML = 
 '<div class="text-center py-8 animate-fade-in"><div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-inbox text-3xl text-gray-400"></i></div><p class="text-gray-500 font-medium">Tidak ada data antrian.</p></div>'; return;
 }
 
 var html = results.map(function(antrian, index) {
 // Daftarkan ke registry agar tombol Lihat dapat membuka detail
 var regKey = antrian.nomor_antrian || ('idx-' + index);
 window.__antrianRegistry[regKey] = antrian;
 var statusColors = {
 'Menunggu': { bg: 'bg-amber-100', text: 'text-amber-700', border: 'border-amber-200', hex: '#f59e0b' },
 'Dokumen Diterima': { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200', hex: '#22c55e' },
 'Verifikasi Data': { bg: 'bg-indigo-100', text: 'text-indigo-700', border: 'border-indigo-200', hex: '#6366f1' },
 'Proses Cetak': { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-200', hex: '#a855f7' },
 'Siap Pengambilan': { bg: 'bg-teal-100', text: 'text-teal-700', border: 'border-teal-200', hex: '#14b8a6' },
 'Berkas Siap Diunduh': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', hex: '#10b981' },
 'Selesai': { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200', hex: '#22c55e' },
 'Ditolak': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', hex: '#ef4444' },
 'Tolak': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', hex: '#ef4444' },
 'Dibatalkan': { bg: 'bg-rose-100', text: 'text-rose-700', border: 'border-rose-200', hex: '#f43f5e' }
 };
 var statusIcons = {
 'Menunggu': 'fa-clock',
 'Dokumen Diterima': 'fa-file-check',
 'Verifikasi Data': 'fa-search',
 'Proses Cetak': 'fa-print',
 'Siap Pengambilan': 'fa-box-open',
 'Berkas Siap Diunduh': 'fa-cloud-download-alt',
 'Selesai': 'fa-check-double',
 'Ditolak': 'fa-ban',
 'Dibatalkan': 'fa-times'
 };

 // Konfigurasi khusus untuk status LayananPernikahan
 var statusPernikahanConfig = {
 'MENUNGGU_KONFIRMASI_KEAGAMAAN': { bg: 'bg-yellow-100', text: 'text-yellow-700', border: 'border-yellow-200', hex: '#f59e0b', label: 'Menunggu Konfirmasi Keagamaan', icon: 'fa-clock' },
 'DITOLAK_KEAGAMAAN': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-200', hex: '#ef4444', label: 'Ditolak', icon: 'fa-times-circle' },
 'MENUNGGU_APPROVE_TANGGAL': { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200', hex: '#3b82f6', label: 'Menunggu Persetujuan Tanggal', icon: 'fa-calendar-check' },
 'TANGGAL_DITOLAK': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Tanggal Ditolak', icon: 'fa-calendar-times' },
 'TANGGAL_DISETUJUI': { bg: 'bg-green-100', text: 'text-green-700', border: 'border-green-200', hex: '#22c55e', label: 'Tanggal Disetujui', icon: 'fa-check-circle' },
 'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI': { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-200', hex: '#a855f7', label: 'Menunggu Verifikasi Dokumen', icon: 'fa-search' },
 'DOKUMEN_PERLU_PERBAIKAN': { bg: 'bg-orange-100', text: 'text-orange-700', border: 'border-orange-200', hex: '#f97316', label: 'Dokumen Perlu Perbaikan', icon: 'fa-exclamation-triangle' },
 'DOKUMEN_DIVERIFIKASI': { bg: 'bg-teal-100', text: 'text-teal-700', border: 'border-teal-200', hex: '#14b8a6', label: 'Dokumen Diverifikasi', icon: 'fa-clipboard-check' },
 'SELESAI': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-200', hex: '#10b981', label: 'Selesai', icon: 'fa-check-double' }
 };

 var nomorAntrian = antrian.nomor_antrian || '-';
 var namaLengkap = antrian.nama_lengkap || '-';
 var namaLayanan = (antrian.layanan && antrian.layanan.nama_layanan) ? antrian.layanan.nama_layanan : 'Layanan Umum';

 // Jika ada data pernikahan, prioritaskan status pernikahan agar real-time
 var hasPernikahan = !!(antrian.pernikahan && antrian.pernikahan.status);
 var statusAntrian, statusStyle, icon;

 if (hasPernikahan) {
 var pCfg = statusPernikahanConfig[antrian.pernikahan.status] || statusPernikahanConfig['MENUNGGU_KONFIRMASI_KEAGAMAAN'];
 statusAntrian = antrian.pernikahan.status_label || pCfg.label;
 statusStyle = { bg: pCfg.bg, text: pCfg.text, border: pCfg.border, hex: pCfg.hex };
 icon = pCfg.icon;
 } else {
 statusAntrian = antrian.status_antrian || 'Menunggu';
 statusStyle = statusColors[statusAntrian] || statusColors['Menunggu'];
 icon = statusIcons[statusAntrian] || 'fa-info-circle';
 }

 var prefixText = nomorAntrian.substring(0, 2);

 // Progress selaras status pernikahan + riwayat lacak_berkas
 var progressInfo = hasPernikahan
 ? window.resolvePernikahanProgress(antrian.pernikahan, antrian)
 : window.resolveAntrianProgress(antrian);
 var isDitolak = progressInfo.isDitolak;
 var isDibatalkan = hasPernikahan ? false : progressInfo.isDibatalkan;
 var isTerminal = progressInfo.isTerminal;
 var currentStep = progressInfo.currentStep;
 var stepWidth = progressInfo.stepWidth;
 var progressGradient = progressInfo.progressGradient
 ? progressInfo.progressGradient
 : (isDitolak ? 'from-red-500 to-rose-600' : (isDibatalkan ? 'from-rose-400 to-rose-600' : 'from-green-500 to-emerald-500'));
 var progressSubtitleHtml = progressInfo.progressSubtitle
 ? '<p class="text-xs text-red-700 mt-1 font-medium">' + progressInfo.progressSubtitle + '</p>'
 : '';
 var progressHtml = '<div class="mt-3">' +
 '<div class="flex justify-between text-xs text-gray-500 mb-1">' +
 '<span>Progress</span>' +
 '<span>' + progressInfo.progressLabel + '</span>' +
 '</div>' +
 '<div class="w-full bg-gray-200 rounded-full h-2 relative overflow-hidden">' +
 '<div class="bg-gradient-to-r ' + progressGradient + ' h-2 rounded-full transition-all duration-500" style="width: ' + stepWidth + '%"></div>' +
 (isTerminal ? '<div class="absolute top-0 h-2 w-1 bg-red-800 rounded-full" style="left: calc(' + stepWidth + '% - 2px)"></div>' : '') +
 '</div>' +
 progressSubtitleHtml +
 '</div>';

 // Alasan Penolakan block (hanya untuk status Ditolak)
 var alasanPenolakanHtml = '';
 if (isDitolak) {
 var alasanExtracted = window.extractAlasanPenolakan(antrian);
 var alasanText = alasanExtracted || 'Alasan tidak dicantumkan oleh petugas.';
 var alasanEscaped = String(alasanText)
 .replace(/&/g, '&amp;')
 .replace(/</g, '&lt;')
 .replace(/>/g, '&gt;')
 .replace(/"/g, '&quot;')
 .replace(/'/g, '&#39;');
 alasanPenolakanHtml = '<div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">' +
 '<div class="flex items-start gap-2">' +
 '<i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>' +
 '<div class="flex-1 min-w-0">' +
 '<p class="font-semibold text-red-700 text-sm">Alasan Penolakan</p>' +
 '<p class="text-sm text-red-900 mt-1 break-words">' + alasanEscaped + '</p>' +
 '</div>' +
 '</div>' +
 '</div>';
 }

 // Info grid (NIK + Tanggal Pengajuan)
 var nikText = antrian.nik || '-';
 var tglPengajuan = '-';
 if (antrian.created_at) {
 try {
 tglPengajuan = new Date(antrian.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
 } catch (e) { tglPengajuan = antrian.created_at; }
 }
 var infoGridHtml = '<div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs text-gray-600">' +
 '<div><i class="fas fa-id-card mr-1 text-green-500"></i>' + nikText + '</div>' +
 '<div><i class="fas fa-calendar mr-1 text-green-500"></i>' + tglPengajuan + '</div>' +
 '</div>';

 var timelineHtml = '';
 if (progressInfo.lacakSorted.length > 0) {
 var timelineColors = hasPernikahan ? window.PERNIKAHAN_LACAK_COLORS : statusColors;
 timelineHtml = window.buildLacakTimelineHtml(progressInfo.lacakSorted, timelineColors);
 }

 // Dokumen final dari pernikahan (Akta + 3 KK)
 var dokumenFinalHtml = '';
 if (hasPernikahan && antrian.pernikahan.dokumen_final) {
 var df = antrian.pernikahan.dokumen_final;
 var dfItems = [
 { key: 'akta_pernikahan', label: 'Akta Pernikahan' },
 { key: 'kk_pasangan', label: 'KK Baru  -  Pasangan' },
 { key: 'kk_ortu_pria', label: 'KK Baru  -  Ortu Pria' },
 { key: 'kk_ortu_wanita', label: 'KK Baru  -  Ortu Wanita' }
 ];
 var anyUploaded = dfItems.some(function(it) { return !!df[it.key]; });

 if (anyUploaded) {
 var rows = dfItems.map(function(it) {
    var url = df[it.key];
    if (url) {
        var downloadUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + 'download=1';
        return '<div style="display:flex;align-items:center;justify-content:space-between;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:8px 12px;margin-bottom:6px;">' +
            '<span style="font-size:12px;color:#374151;font-weight:500;display:flex;align-items:center;gap:6px;">' +
            '<i class="fas fa-file-pdf" style="color:#059669;"></i>' + it.label + '</span>' +
            '<a href="' + downloadUrl + '" onclick="event.stopPropagation()" ' +
            'style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#ffffff;font-size:12px;font-weight:600;padding:5px 12px;border-radius:6px;text-decoration:none;white-space:nowrap;">' +
            '<i class="fas fa-download"></i> Download</a>' +
            '</div>';
    }
    return '<div style="display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;margin-bottom:6px;">' +
        '<span style="font-size:12px;color:#9ca3af;display:flex;align-items:center;gap:6px;">' +
        '<i class="fas fa-file-pdf" style="color:#d1d5db;"></i>' + it.label + '</span>' +
        '<span style="font-size:12px;color:#9ca3af;font-style:italic;">Belum tersedia</span>' +
        '</div>';
}).join('');

 var uploadedAt = antrian.pernikahan.dokumen_final_uploaded_at
 ? '<p class="text-[10px] text-gray-400 mt-2"><i class="fas fa-clock mr-1"></i>Diupload: ' + antrian.pernikahan.dokumen_final_uploaded_at + '</p>'
 : '';

 dokumenFinalHtml = '<div class="mt-4 pt-4 border-t border-gray-100">' +
 '<p class="text-xs font-semibold text-gray-700 mb-2"><i class="fas fa-folder-open text-emerald-600 mr-1"></i>Dokumen Hasil Penerbitan Disdukcapil</p>' +
 '<div class="space-y-2">' + rows + '</div>' +
 uploadedAt +
 '</div>';
 }
 }

 // Override warna header card untuk status terminal
 var headerIconGradient = isDitolak ? 'from-red-500 to-rose-600' : (isDibatalkan ? 'from-rose-400 to-rose-600' : 'from-green-500 to-emerald-600');
 var headerAccentText = isDitolak ? 'text-red-600' : (isDibatalkan ? 'text-rose-600' : 'text-green-600');
 var headerAccentBg = isDitolak ? 'bg-red-100' : (isDibatalkan ? 'bg-rose-100' : 'bg-green-100');

 return '<div class="search-result-card bg-white border-2 ' + statusStyle.border + ' rounded-xl p-5 shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer" style="animation-delay: ' + (index * 0.1) + 's" data-card-antrian-key="' + regKey + '">' +
 '<div class="flex items-center gap-2 mb-3">' +
 '<div class="w-12 h-12 bg-gradient-to-br ' + headerIconGradient + ' rounded-xl flex items-center justify-center text-white shadow-lg">' +
 '<i class="fas fa-file-alt text-xl"></i>' +
 '</div>' +
 '<div class="flex-1 min-w-0">' +
 '<div class="flex items-center gap-2 mb-1">' +
 '<span class="text-xs font-semibold ' + headerAccentText + ' ' + headerAccentBg + ' px-2 py-0.5 rounded uppercase tracking-wide">' + namaLayanan + '</span>' +
 '</div>' +
 '<h3 class="font-bold text-xl ' + headerAccentText + ' truncate">' + nomorAntrian + '</h3>' +
 '<p class="text-gray-800 font-semibold truncate">' + namaLengkap + '</p>' +
 '</div>' +
 '<div class="flex items-center gap-2 px-3 py-2 rounded-full ' + statusStyle.bg + ' ' + statusStyle.text + ' border ' + statusStyle.border + ' font-bold text-xs shadow-sm whitespace-nowrap">' +
 '<i class="fas ' + icon + '"></i>' +
 '<span>' + statusAntrian + '</span>' +
 '</div>' +
 '</div>' +
 progressHtml +
 infoGridHtml +
 alasanPenolakanHtml +
 timelineHtml +
 dokumenFinalHtml +
 (!isTerminal ? '<div class="mt-4 pt-3 border-t border-gray-100 flex justify-end">' +
 '<button type="button" data-action="lihat-antrian" data-antrian-key="' + regKey + '" class="px-4 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm inline-flex items-center gap-2 transition-all">' +
 '<i class="fas fa-eye"></i><span>Lihat</span>' +
 '</button>' +
 '</div>' : '') +
 '</div>';
 }).join('');
 document.getElementById('searchResults').innerHTML = html;
 };
</script>

<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
<script>
/* Layanan Mandiri Scripts */
window.serviceConfig = @json($serviceConfig);
const layananById = @json($layananById);

function navigateToLayanan() {
    var btn = document.getElementById('goToLayananBtn');
    if (!btn) return;
    var ticketNumber = btn.getAttribute('data-ticket-number') || '';
    var layananId = btn.getAttribute('data-layanan-id') || '';

    if (!ticketNumber) {
        toastError('Nomor antrian kosong.', 'Silakan ambil antrian terlebih dahulu.');
        return;
    }

    // 1. Salin nomor antrian
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(ticketNumber).catch(function(err) {
            console.warn('Gagal menyalin nomor antrian: ', err);
            if (typeof fallbackCopyNomor === 'function') fallbackCopyNomor(ticketNumber);
        });
    } else {
        if (typeof fallbackCopyNomor === 'function') fallbackCopyNomor(ticketNumber);
    }

    // Tampilkan Toast sukses copy + transition
    toastSuccess('Nomor Antrian Disalin', 'Nomor antrian <strong>' + ticketNumber + '</strong> telah disalin ke clipboard.');

    // 2. Scroll ke Layanan Mandiri Section
    var section = document.getElementById('layananMandiriSection');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }

    // 3. Cari config layanan yang sesuai dan buka modal
    if (layananId && window.serviceConfig && window.serviceConfig[layananId]) {
        var config = window.serviceConfig[layananId];
        var serviceName = config.is_pernikahan ? 'Akte Perkawinan' : (layananById[layananId] ? layananById[layananId].nama_layanan : 'Layanan');

        // Buka modal secara delay agar scroll smooth selesai
        setTimeout(function() {
            openServiceModal(config, serviceName);

            // 4. Auto-fill nomor antrian dan fetch data
            setTimeout(function() {
                var input = document.getElementById('nomorAntrianInput');
                if (input) {
                    input.value = ticketNumber;
                    autoFillFromAntrian(ticketNumber);
                }
            }, 500);
        }, 600);
    }
}

let lmCurrentStep        = 1;
let lmCurrentConfig      = {};
let lmCurrentServiceName = '';
let mpCamera             = null;
let faceMeshInstance     = null;
let blinkCount           = 0;
let eyeClosed            = false;
let livenessStarted      = false;

const BLINK_THRESHOLD    = 0.25;
const BLINK_TARGET       = 2;
const SUBMIT_TIMEOUT_MS  = 120000;

const lmRouteMap = {
    'kk':                 "{{ route('kk.store') }}",
    'akte_kelahiran':     "{{ route('aktelahir.store') }}",
    'ganti_kepala_kk':    "{{ route('kk.store.gantikepalakk') }}",
    'kk_hilang_rusak':    "{{ route('kk.store.hilangrusak') }}",
    'pisah_kk':           "{{ route('kk.store.pisahkk') }}",
    'akte_kematian':      "{{ route('akte-kematian.store') }}",
    'lahir_mati':         "{{ route('lahir-mati.store') }}",
    'layanan-pernikahan': "{{ route('pernikahan.store.layanan-mandiri') }}"
};

const LEFT_EYE_IDX  = [33, 160, 158, 133, 153, 144];
const RIGHT_EYE_IDX = [362, 385, 387, 263, 373, 380];

function showLoadingModal(title, text) {
    if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
        Swal.close();
    }
    return Swal.fire({
        title             : title  || 'Memproses...',
        text              : text   || 'Mohon tunggu sebentar',
        allowOutsideClick : false,
        allowEscapeKey    : false,
        showConfirmButton : false,
        showDenyButton    : false,
        showCancelButton  : false,
        didOpen           : function() { Swal.showLoading(); }
    });
}

function getColorText(color) {
    var map = { blue:'#1D4ED8', green:'#15803D', orange:'#C2410C', purple:'#7E22CE', red:'#BE123C' };
    return map[color] || map.blue;
}
function getColorBadgeBg(color) {
    var map = { blue:'#DBEAFE', green:'#DCFCE7', orange:'#FFEDD5', purple:'#F3E8FF', red:'#FFE4E6' };
    return map[color] || map.blue;
}

function isPemohonIdentityField(name) {
    return ['nama_pemohon', 'nik_pemohon', 'alamat_pemohon', 'alamat'].indexOf(name) !== -1;
}

function renderField(field) {
    var cls = 'form-input';
    var extraAttr = '';
    var fieldId = '';
    var fieldEvents = '';
    var isReadonlyPemohon = isPemohonIdentityField(field.name);
    var readonlyAttr = isReadonlyPemohon ? 'readonly aria-readonly="true"' : '';
    var inputCls = cls + (isReadonlyPemohon ? ' form-input--readonly' : '');

    if (field.name === 'nomor_antrian') {
        fieldId = 'id="nomorAntrianInput"';
        fieldEvents = 'onchange="autoFillFromAntrian(this.value)"';
    } else if (field.name === 'nik_pemohon') {
        fieldId = 'id="nikPemohonInput"';
    } else if (field.name === 'nama_pemohon') {
        fieldId = 'id="namaPemohonInput"';
    } else if (field.name === 'alamat_pemohon' || field.name === 'alamat') {
        fieldId = 'id="alamatPemohonInput"';
    }

    if (!isReadonlyPemohon && field.name && (field.name.toLowerCase().includes('nik') || field.name.toLowerCase().includes('nomor_kk'))) {
        extraAttr = 'oninput="this.value = this.value.replace(/[^0-9]/g, \'\').slice(0, 16);" maxlength="16"';
    }
    var wajibAttr = field.required !== false ? ' data-wajib="true"' : '';
    var skipSecurity = isReadonlyPemohon || field.type === 'hidden' || field.type === 'file';
    var securityAttr = skipSecurity ? '' : ' data-validate-security="true"';
    if (field.type === 'textarea')
        return '<textarea name="' + field.name + '" placeholder="' + (field.placeholder||'') + '" class="' + inputCls + ' h-24 resize-none" ' + fieldId + ' ' + readonlyAttr + wajibAttr + securityAttr + '></textarea>';
    if (field.type === 'select')
        return '<select name="' + field.name + '" class="' + inputCls + '" ' + fieldId + wajibAttr + securityAttr + '>' +
               '<option value="">Pilih...</option>' +
               (field.options||[]).map(function(o){ return '<option value="' + o + '">' + o + '</option>'; }).join('') +
               '</select>';
    return '<input type="' + field.type + '" name="' + field.name + '" placeholder="' + (field.placeholder||'') + '" class="' + inputCls + '" ' + fieldId + ' ' + extraAttr + ' ' + fieldEvents + ' ' + readonlyAttr + wajibAttr + securityAttr + '>';
}

function computeEAR(p1,p2,p3,p4,p5,p6) {
    var d = function(a,b) { return Math.hypot(a.x-b.x, a.y-b.y); };
    return (d(p2,p6) + d(p3,p5)) / (2 * d(p1,p4));
}

function getEAR(lm) {
    var l = function(i) { return lm[i]; };
    var earL = computeEAR(l(LEFT_EYE_IDX[0]),l(LEFT_EYE_IDX[1]),l(LEFT_EYE_IDX[2]),l(LEFT_EYE_IDX[3]),l(LEFT_EYE_IDX[4]),l(LEFT_EYE_IDX[5]));
    var earR = computeEAR(l(RIGHT_EYE_IDX[0]),l(RIGHT_EYE_IDX[1]),l(RIGHT_EYE_IDX[2]),l(RIGHT_EYE_IDX[3]),l(RIGHT_EYE_IDX[4]),l(RIGHT_EYE_IDX[5]));
    return (earL + earR) / 2;
}

function detectBlink(landmarks) {
    var ear = getEAR(landmarks);
    if (ear < BLINK_THRESHOLD && !eyeClosed) { eyeClosed = true; }
    else if (ear >= BLINK_THRESHOLD && eyeClosed) {
        eyeClosed = false;
        blinkCount++;
        updateBlinkUI();
        if (blinkCount >= BLINK_TARGET) onLivenessPassed();
    }
}

function stopCamera() { if (mpCamera) { mpCamera.stop(); mpCamera = null; } }

function openKategoriModal(layananList, namaKategori, colors, iconClass) {
    document.getElementById('km-title').textContent = namaKategori;
    document.getElementById('km-sub').textContent   = layananList.length + ' layanan tersedia';

    var iconEl = document.getElementById('km-icon');
    iconEl.style.background = colors.icon_bg;
    iconEl.innerHTML = '<i class="fas ' + iconClass + ' text-lg" style="color:' + colors.text + '"></i>';

    var list = document.getElementById('km-list');
    list.innerHTML = layananList.map(function(item) {
        return '<div class="layanan-item flex items-center gap-3 px-3 py-3 rounded-xl cursor-pointer"' +
               ' onclick=\'selectLayanan(' + JSON.stringify(item.config) + ', ' + JSON.stringify(item.name) + ')\'>' +
               '<div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"' +
               ' style="background: ' + colors.icon_bg + '">' +
               '<i class="fas ' + item.icon + ' text-sm" style="color:' + colors.text + '"></i>' +
               '</div>' +
               '<div class="flex-1 min-w-0">' +
               '<p class="text-sm font-semibold text-gray-800 leading-tight">' + item.name + '</p>' +
               '<p class="text-xs text-gray-400 mt-0.5 truncate">' + item.desc + '</p>' +
               '</div>' +
               '<div class="flex-shrink-0">' +
               '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold"' +
               ' style="background: ' + colors.badge_bg + '; color: ' + colors.badge_text + '">' +
               'Pilih <i class="fas fa-chevron-right text-[9px]"></i>' +
               '</span>' +
               '</div>' +
               '</div>';
    }).join('');

    document.getElementById('kategoriModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeKategoriModal() {
    document.getElementById('kategoriModal').classList.add('hidden');
    if (document.getElementById('serviceModal').classList.contains('hidden')) {
        document.body.style.overflow = 'auto';
    }
}

function selectLayanan(config, serviceName) {
    closeKategoriModal();
    openServiceModal(config, serviceName);
}

function openServiceModal(config, serviceName) {
    lmCurrentConfig      = config;
    lmCurrentServiceName = serviceName;

    document.getElementById('modalTitle').textContent = serviceName;
    var icon = document.getElementById('modalIcon');
    icon.style.background = getColorBadgeBg(config.color);
    icon.innerHTML = '<i class="fas ' + config.icon + ' text-xl" style="color:' + getColorText(config.color) + '"></i>';

    document.getElementById('lmServiceForm').action = lmRouteMap[config.id] || '#';

    document.getElementById('lmInfoLayanan').textContent =
        'Layanan ' + serviceName + ' adalah layanan kependudukan yang dapat diajukan secara online melalui portal Disdukcapil Kabupaten Toba. Proses verifikasi dilakukan oleh petugas dalam 2–3 hari kerja.';

    document.getElementById('lmListPersyaratan').innerHTML = config.persyaratan.map(function(p, i) {
        return '<li class="flex items-start gap-3 bg-white border border-gray-100 rounded-xl p-3">' +
               '<div class="w-5 h-5 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">' +
               '<span class="text-orange-600 font-bold text-[10px]">' + (i+1) + '</span>' +
               '</div>' +
               '<span class="text-sm text-gray-700 leading-relaxed">' + p + '</span>' +
               '</li>';
    }).join('');

    document.getElementById('lmListPenjelasan').innerHTML = config.penjelasan.map(function(p, i) {
        return '<li class="flex items-start gap-3">' +
               '<div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">' +
               '<span class="text-white font-bold text-[10px]">' + (i+1) + '</span>' +
               '</div>' +
               '<span class="text-sm text-gray-700 leading-relaxed">' + p + '</span>' +
               '</li>';
    }).join('');

    var hiddenAndText = config.fields.filter(function(f) { return f.type !== 'file'; });
    document.getElementById('lmFormFields').innerHTML = hiddenAndText.map(function(field) {
        if (field.type === 'hidden')
            return '<input type="hidden" name="' + field.name + '" value="' + field.value + '">';
        if (field.type === 'heading')
            return '<div class="col-span-1 md:col-span-2 mt-4 mb-1 border-b border-gray-200 pb-2">' +
                   '<h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">' + field.label + '</h3>' +
                   '</div>';
        var fullWidth = field.type === 'textarea' ? 'md:col-span-2' : '';
        return '<div class="' + fullWidth + '">' +
               '<label class="block text-xs font-semibold text-gray-600 mb-1">' +
               field.label + ' <span class="text-red-400">*</span>' +
               '</label>' +
               renderField(field) +
               '</div>';
    }).join('');

    if (config.is_pernikahan) {
        document.getElementById('lmFileFields').innerHTML =
            '<div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-4">' +
            '<h4 class="font-semibold text-purple-800 text-sm mb-3 flex items-center gap-2">' +
            '<i class="fas fa-church"></i> Data Pernikahan</h4>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
            '<div>' +
            '<label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Agama <span class="text-red-400">*</span></label>' +
            '<select name="jenis_agama" id="jenisAgamaSelect" class="form-input" data-wajib="true" data-validate-security="true" onchange="loadKeagamaanByAgama(this.value)">' +
            '<option value="">Pilih Agama...</option>' +
            '</select>' +
            '</div>' +
            '<div>' +
            '<label class="block text-xs font-semibold text-gray-700 mb-1">Nama Tempat Keagamaan <span class="text-red-400">*</span></label>' +
            '<select name="keagamaan_id" id="keagamaanSelect" class="form-input" data-wajib="true" data-validate-security="true" disabled>' +
            '<option value="">Pilih agama terlebih dahulu...</option>' +
            '</select>' +
            '</div>' +
            '<div>' +
            '<label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Perkawinan (Rencana) <span class="text-red-400">*</span></label>' +
            '<input type="date" name="tanggal_perkawinan" class="form-input" data-wajib="true" data-validate-security="true" min="' + getMinDate() + '">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<h4 class="font-semibold text-gray-700 text-sm mb-3 flex items-center gap-2">' +
            '<i class="fas fa-file-upload text-blue-500"></i> Upload KTP</h4>' +
            '<div class="grid grid-cols-2 gap-4">' +
            config.files.map(function(file) {
                var reqLabel = file.required !== false
                    ? '<span class="text-red-400">*</span>'
                    : '<span class="text-gray-400 font-normal">(opsional)</span>';
                return '<div>' +
                       '<label class="block text-xs font-semibold text-gray-600 mb-1">' + file.label + ' ' + reqLabel + '</label>' +
                       '<div class="relative">' +
                       '<button type="button" id="clear-' + file.name + '"' +
                       ' class="hidden absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs flex items-center justify-center shadow-md transition"' +
                       ' title="Hapus file" onclick="event.preventDefault(); event.stopPropagation(); clearFileInput(\'' + file.name + '\')">' +
                       '<i class="fas fa-times"></i></button>' +
                       '<label class="flex flex-col items-center justify-center w-full px-3 py-4' +
                       ' border-2 border-dashed border-gray-300 rounded-xl bg-gray-50' +
                       ' hover:bg-blue-50 hover:border-blue-400 transition-all cursor-pointer">' +
                       '<i class="fas fa-file-image text-xl text-gray-400 mb-1" id="icon-' + file.name + '"></i>' +
                       '<p class="text-xs font-semibold text-gray-600">Pilih File</p>' +
                       '<p class="text-[9px] text-gray-400 mt-1">PDF/Gambar, maks. 2MB</p>' +
                       '<input type="file" name="' + file.name + '" accept=".pdf,.jpg,.jpeg,.png"' +
                       (file.required !== false ? ' data-wajib="true"' : '') +
                       ' class="hidden" onchange="handleFileSelect(this,\'' + file.name + '\')">' +
                       '</label>' +
                       '</div>' +
                       '<div id="name-' + file.name + '" class="mt-1 px-2 text-[10px] text-blue-600 font-medium hidden">' +
                       '<i class="fas fa-check-circle mr-1"></i><span class="file-label"></span>' +
                       '</div>' +
                       '</div>';
            }).join('') +
            '</div>';

        loadJenisAgama();
    } else {
        document.getElementById('lmFileFields').innerHTML = config.files.map(function(file) {
            var reqLabel = file.required !== false
                ? '<span class="text-red-400">*</span>'
                : '<span class="text-gray-400 font-normal">(opsional)</span>';
            return '<div>' +
                   '<label class="block text-xs font-semibold text-gray-600 mb-1">' + file.label + ' ' + reqLabel + '</label>' +
                   '<div class="relative">' +
                   '<button type="button" id="clear-' + file.name + '"' +
                   ' class="hidden absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs flex items-center justify-center shadow-md transition"' +
                   ' title="Hapus file" onclick="event.preventDefault(); event.stopPropagation(); clearFileInput(\'' + file.name + '\')">' +
                   '<i class="fas fa-times"></i></button>' +
                   '<label class="flex flex-col items-center justify-center w-full px-4 py-5' +
                   ' border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50' +
                   ' hover:bg-blue-50 hover:border-blue-400 transition-all cursor-pointer">' +
                   '<i class="fas fa-file-pdf text-2xl text-gray-400 mb-2" id="icon-' + file.name + '"></i>' +
                   '<p class="text-sm font-semibold text-gray-600">Pilih File PDF</p>' +
                   '<p class="text-[10px] text-gray-400 mt-1 uppercase tracking-wider">Format: PDF, maks. 2MB</p>' +
                   '<input type="file" name="' + file.name + '" accept=".pdf"' +
                   (file.required !== false ? ' data-wajib="true"' : '') +
                   ' class="hidden" onchange="handleFileSelect(this,\'' + file.name + '\')">' +
                   '</label>' +
                   '</div>' +
                   '<div id="name-' + file.name + '" class="mt-1.5 px-2 text-[11px] text-blue-600 font-medium hidden">' +
                   '<i class="fas fa-check-circle mr-1"></i><span class="file-label"></span>' +
                   '</div>' +
                   '</div>';
        }).join('');
    }

    var step3Desc = document.getElementById('lmStep3Description');
    if (config.is_pernikahan) {
        step3Desc.innerHTML = 'Pilih agama dan tempat keagamaan, tentukan tanggal perkawinan, lalu upload <strong>KTP</strong> mempelai dan saksi.';
    } else {
        step3Desc.innerHTML = 'Upload berkas persyaratan dalam format <strong>PDF</strong>. Pastikan dokumen terbaca dengan jelas.';
    }

    resetLiveness();
    lmGoToStep(1);
    document.getElementById('serviceModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function lmGoToStep(step) {
    lmCurrentStep = step;
    document.querySelectorAll('#lmServiceForm .step-content').forEach(function(el) { el.classList.add('hidden'); });
    var active = document.getElementById('lmStep' + step);
    if (active) {
        active.classList.remove('hidden');
        active.style.animation = 'none';
        active.offsetHeight;
        active.style.animation = '';
    }
    var labels = ['Informasi','Data','Berkas','Verifikasi','Konfirmasi'];
    for (var i = 1; i <= 5; i++) {
        var dot = document.getElementById('lmStepDot' + i);
        var lbl = document.getElementById('lmStepLabel' + i);
        if (!dot || !lbl) continue;
        dot.className = 'lm-step-indicator w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold mb-1 transition-all duration-300';
        lbl.className = 'text-[9px] font-semibold lm-step-label text-gray-400';
        if (i < step)        { dot.classList.add('done');   dot.innerHTML = '<i class="fas fa-check text-[10px]"></i>'; lbl.classList.add('done'); }
        else if (i === step) { dot.classList.add('active'); dot.textContent = i; lbl.classList.add('active'); }
        else                 { dot.textContent = i; }
        if (i < 5) {
            var line = document.getElementById('lmStepLine' + i);
            if (line) line.className = 'flex-1 h-0.5 rounded mb-5 transition-all duration-500 ' + (i < step ? 'bg-green-400' : 'bg-gray-200');
        }
    }
    document.getElementById('lmModalStepLabel').textContent = 'Langkah ' + step + ' dari 5 – ' + labels[step-1];
    document.getElementById('lmModalContent').scrollTop = 0;
    if (step === 4) {
        stopCamera();
        livenessStarted = false;
        faceMeshInstance = null;
        updateLivenessStepUI();
    }
    if (step === 5) lmBuildSummary();
}

function lmValidateAndGoStep3() {
    var inputs = document.getElementById('lmStep2')
        .querySelectorAll('[data-wajib]:not([data-wajib="false"])');
    var valid = true;
    var hasEmpty = false;
    var errMsg = 'Ada Dokumen yang Perlu dilengkapi';
    inputs.forEach(function(input) {
        input.style.borderColor = '';
        var val = input.value.trim();
        if (!val) { input.style.borderColor = '#ef4444'; valid = false; hasEmpty = true; }
        else if (val && (input.name.toLowerCase().includes('nik') || input.name.toLowerCase().includes('nomor_kk')) && val.length !== 16) {
            input.style.borderColor = '#ef4444';
            valid = false;
            errMsg = 'Nomor harus tepat 16 angka!';
        }
    });
    if (hasEmpty) errMsg = 'Ada Data yang Perlu dilengkapi';
    if (!valid) {
        if (errMsg.indexOf('16 angka') !== -1) {
            toastError('Nomor harus tepat 16 angka.', 'Masukkan NIK atau nomor KK sesuai dokumen kependudukan (16 digit).');
        } else {
            toastError('Ada kolom wajib yang belum diisi.', 'Lengkapi semua data pada langkah ini, lalu lanjutkan.');
        }
        return;
    }
    lmGoToStep(3);
}

function lmValidateAndGoStep4() {
    var valid = true;
    var missingLabel = '';
    lmCurrentConfig.files.forEach(function(file) {
        if (file.required === false) return;
        var input = document.querySelector('input[name="' + file.name + '"]');
        if (!input || !input.files || input.files.length === 0) {
            if (!missingLabel) missingLabel = file.label;
            valid = false;
            var lbl = input ? input.closest('label') : null;
            if (lbl) {
                lbl.classList.add('border-red-400', 'bg-red-50');
                input.addEventListener('change', function() { lbl.classList.remove('border-red-400','bg-red-50'); }, { once: true });
            }
        }
    });
    if (!valid) {
        toastError(
            'Dokumen wajib belum diunggah' + (missingLabel ? ': ' + missingLabel : '') + '.',
            'Unggah semua berkas PDF yang wajib, lalu lanjutkan ke langkah berikutnya.'
        );
        return;
    }
    lmGoToStep(4);
}

function lmBuildSummary() {
    var html = '';
    lmCurrentConfig.fields.forEach(function(f) {
        if (f.type === 'hidden' || f.type === 'file' || f.type === 'heading') return;
        var el  = document.querySelector('[name="' + f.name + '"]');
        var val = el ? el.value : '-';
        html += '<div class="flex justify-between py-1.5 border-b border-gray-100 last:border-0">' +
                '<span class="text-gray-500 text-xs">' + f.label + '</span>' +
                '<span class="font-semibold text-gray-800 text-xs text-right max-w-[60%] truncate">' + (val||'-') + '</span>' +
                '</div>';
    });
    lmCurrentConfig.files.forEach(function(f) {
        var el  = document.querySelector('[name="' + f.name + '"]');
        var val = el && el.files[0] ? el.files[0].name : '(belum dipilih)';
        html += '<div class="flex justify-between py-1.5 border-b border-gray-100 last:border-0">' +
                '<span class="text-gray-500 text-xs">' + f.label + '</span>' +
                '<span class="font-semibold text-xs text-right max-w-[60%] truncate ' + (el&&el.files[0]?'text-green-600':'text-gray-400') + '">' + val + '</span>' +
                '</div>';
    });
    document.getElementById('lmSummaryData').innerHTML = html;
}

function updateBlinkUI() {
    document.getElementById('lmBlinkCount').textContent = blinkCount;
    for (var i = 1; i <= BLINK_TARGET; i++) {
        var dot = document.getElementById('lmBlinkDot' + i);
        if (!dot) continue;
        if (i <= blinkCount) {
            dot.className = 'w-8 h-8 rounded-full border-2 border-green-500 bg-green-500 flex items-center justify-center text-xs font-bold text-white';
            dot.innerHTML = '<i class="fas fa-check text-xs"></i>';
        }
    }
    setOverlay('Kedipan ' + blinkCount + '/' + BLINK_TARGET + ' terdeteksi...');
}

function onLivenessPassed() {
    var video  = document.getElementById('lmVideo');
    var canvas = document.getElementById('lmCanvas');
    canvas.width  = video.videoWidth  || 640;
    canvas.height = video.videoHeight || 480;
    var ctx = canvas.getContext('2d');
    ctx.translate(canvas.width, 0); ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    document.getElementById('lm_foto_wajah').value = canvas.toDataURL('image/jpeg', 0.85);
    stopCamera();
    document.getElementById('lm_liveness_passed').value = '1';
    blinkCount = BLINK_TARGET;
    showLivenessCompletedUI();
    toastSuccess('Verifikasi Wajah Berhasil');
    setTimeout(function() { lmGoToStep(5); }, 900);
}

function showLivenessCompletedUI() {
    var fotoWajah = document.getElementById('lm_foto_wajah').value;
    var video = document.getElementById('lmVideo');
    var preview = document.getElementById('lmFotoPreview');
    if (!preview && fotoWajah) {
        preview = document.createElement('img');
        preview.src = fotoWajah;
        preview.className = 'w-full rounded-xl';
        preview.style.maxHeight = '260px';
        preview.style.objectFit = 'cover';
        preview.id = 'lmFotoPreview';
        video.parentNode.insertBefore(preview, video);
    } else if (preview && fotoWajah) {
        preview.src = fotoWajah;
    }
    video.style.display = 'none';
    var overlay = document.getElementById('lmLivenessOverlay');
    overlay.textContent = '✓ Verifikasi wajah selesai';
    overlay.className = 'absolute bottom-0 left-0 right-0 bg-green-600/80 text-white text-center py-2 text-sm font-semibold';
    document.getElementById('lmBlinkCount').textContent = String(BLINK_TARGET);
    for (var i = 1; i <= BLINK_TARGET; i++) {
        var dot = document.getElementById('lmBlinkDot' + i);
        if (dot) {
            dot.className = 'w-8 h-8 rounded-full border-2 border-green-500 bg-green-500 flex items-center justify-center text-xs font-bold text-white';
            dot.innerHTML = '<i class="fas fa-check text-xs"></i>';
        }
    }
    setLivenessActionButton('next');
}

function updateLivenessStepUI() {
    var passed = document.getElementById('lm_liveness_passed').value === '1';
    var fotoWajah = document.getElementById('lm_foto_wajah').value;
    if (passed && fotoWajah) {
        showLivenessCompletedUI();
        return;
    }
    var old = document.getElementById('lmFotoPreview');
    if (old) old.remove();
    document.getElementById('lmVideo').style.display = '';
    var overlay = document.getElementById('lmLivenessOverlay');
    overlay.textContent = 'Tekan "Mulai Verifikasi" untuk mengaktifkan kamera';
    overlay.className = 'absolute bottom-0 left-0 right-0 bg-black/50 text-white text-center py-2 text-sm font-semibold';
    setLivenessActionButton('start');
}

function setOverlay(text) { document.getElementById('lmLivenessOverlay').textContent = text; }

function startLiveness() {
    if (livenessStarted) return;
    livenessStarted = true;
    var errEl = document.getElementById('lmLivenessError');
    errEl.classList.add('hidden');
    document.getElementById('lmBtnStartLiveness').disabled = true;
    setOverlay('Meminta izin kamera...');
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
        stream.getTracks().forEach(function(t) { t.stop(); });
        setOverlay('Mengaktifkan algoritma wajah...');
        var video = document.getElementById('lmVideo');
        faceMeshInstance = new FaceMesh({ locateFile: function(f) { return 'https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/' + f; } });
        faceMeshInstance.setOptions({ maxNumFaces:1, refineLandmarks:true, minDetectionConfidence:0.5, minTrackingConfidence:0.5 });
        faceMeshInstance.onResults(function(results) {
            if (!results.multiFaceLandmarks || !results.multiFaceLandmarks.length) {
                setOverlay('Wajah tidak terdeteksi – pastikan wajah terlihat jelas'); return;
            }
            setOverlay('Kedipkan mata 2 kali secara natural...');
            detectBlink(results.multiFaceLandmarks[0]);
        });
        mpCamera = new Camera(video, {
            onFrame: async function() { if (faceMeshInstance) await faceMeshInstance.send({ image: video }); },
            width: 640, height: 480
        });
        mpCamera.start().then(function() { setOverlay('Kedipkan mata 2 kali secara natural...'); })
            .catch(function(err) {
                errEl.textContent = 'Gagal render MediaPipe: ' + (err.message || err);
                errEl.classList.remove('hidden');
                setOverlay('Gagal mengakses kamera');
                livenessStarted = false;
                document.getElementById('lmBtnStartLiveness').disabled = false;
            });
    }).catch(function(err) {
        errEl.textContent = 'Kamera diblokir. Cek izin browser. (' + err.name + ')';
        errEl.classList.remove('hidden');
        setOverlay('Izin kamera ditolak');
        livenessStarted = false;
        document.getElementById('lmBtnStartLiveness').disabled = false;
    });
}

function resetLiveness() {
    blinkCount = 0; eyeClosed = false; livenessStarted = false; faceMeshInstance = null;
    var old = document.getElementById('lmFotoPreview');
    if (old) old.remove();
    document.getElementById('lmVideo').style.display = '';
    document.getElementById('lm_foto_wajah').value = '';
    document.getElementById('lmBlinkCount').textContent = '0';
    document.getElementById('lm_liveness_passed').value = '0';
    document.getElementById('lmLivenessOverlay').textContent = 'Tekan "Mulai Verifikasi" untuk mengaktifkan kamera';
    document.getElementById('lmLivenessOverlay').className = 'absolute bottom-0 left-0 right-0 bg-black/50 text-white text-center py-2 text-sm font-semibold';
    setLivenessActionButton('start');
    for (var i = 1; i <= BLINK_TARGET; i++) {
        var dot = document.getElementById('lmBlinkDot' + i);
        if (dot) { dot.className = 'w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center text-xs font-bold text-gray-400'; dot.textContent = i; }
    }
}

function setLivenessActionButton(mode) {
    var btn = document.getElementById('lmBtnStartLiveness');
    if (!btn) return;
    if (mode === 'next') {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-arrow-right text-sm"></i> Selanjutnya';
    } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-camera text-sm"></i> Mulai Verifikasi';
    }
}

function getMinDate() {
    var today = new Date();
    var minDate = new Date(today);
    minDate.setDate(today.getDate() + 7);
    return minDate.toISOString().split('T')[0];
}

function loadJenisAgama() {
    fetch("{{ route('api.pernikahan.jenis-agama') }}")
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                var select = document.getElementById('jenisAgamaSelect');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Agama...</option>';
                    data.data.forEach(function(item) {
                        select.innerHTML += '<option value="' + item.jenis_keagamaan_id + '">' + item.nama_jenis_keagamaan + '</option>';
                    });
                }
            }
        })
        .catch(function(error) { console.error('Gagal load jenis agama:', error); });
}

function loadKeagamaanByAgama(jenisAgamaId) {
    var keagamaanSelect = document.getElementById('keagamaanSelect');
    if (!keagamaanSelect) return;
    if (!jenisAgamaId) {
        keagamaanSelect.innerHTML = '<option value="">Pilih agama terlebih dahulu...</option>';
        keagamaanSelect.disabled = true;
        return;
    }
    keagamaanSelect.innerHTML = '<option value="">Memuat data...</option>';
    keagamaanSelect.disabled = true;
    fetch("{{ route('api.pernikahan.keagamaan') }}?jenis_keagamaan_id=" + jenisAgamaId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                keagamaanSelect.innerHTML = '<option value="">Pilih Tempat Keagamaan...</option>';
                if (data.data.length === 0) {
                    keagamaanSelect.innerHTML = '<option value="">Tidak ada data keagamaan untuk agama ini</option>';
                } else {
                    data.data.forEach(function(item) {
                        keagamaanSelect.innerHTML += '<option value="' + item.keagamaan_id + '">' + item.nama_tempat + ' - ' + item.alamat + '</option>';
                    });
                }
                keagamaanSelect.disabled = false;
            } else {
                keagamaanSelect.innerHTML = '<option value="">Gagal memuat data</option>';
            }
        })
        .catch(function(error) {
            console.error('Gagal load keagamaan:', error);
            keagamaanSelect.innerHTML = '<option value="">Gagal memuat data</option>';
        });
}

function stripToastHtml(message) {
    var tmp = document.createElement('div');
    tmp.innerHTML = message || '';
    return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
}

function escapeHtmlText(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function parseJsonResponse(response) {
    var contentType = (response.headers.get('content-type') || '').toLowerCase();
    var text = await response.text();

    if (!text || !text.trim()) {
        return {
            success: false,
            message: response.ok
                ? 'Server tidak mengembalikan respons.'
                : 'Gagal memproses permintaan (HTTP ' + response.status + ').',
            problem: response.status === 419
                ? 'Sesi halaman habis atau token keamanan tidak valid.'
                : 'Server tidak mengembalikan data.',
            solution: response.status === 419
                ? 'Muat ulang halaman, isi ulang formulir, lalu kirim kembali.'
                : 'Periksa koneksi internet dan ukuran berkas, lalu coba lagi.'
        };
    }

    if (contentType.indexOf('application/json') !== -1 || text.trim().charAt(0) === '{') {
        try {
            return JSON.parse(text);
        } catch (e) {
            return {
                success: false,
                message: 'Respons server tidak valid.',
                problem: 'Format respons dari server tidak dapat dibaca.',
                solution: 'Coba lagi. Jika masalah berlanjut, hubungi petugas layanan.'
            };
        }
    }

    return {
        success: false,
        message: 'Gagal memproses permintaan (HTTP ' + response.status + ').',
        problem: response.status === 419
            ? 'Sesi halaman habis atau token keamanan tidak valid.'
            : 'Server mengembalikan respons yang tidak diharapkan.',
        solution: response.status === 419
            ? 'Muat ulang halaman, isi ulang formulir, lalu kirim kembali.'
            : 'Periksa kelengkapan berkas dan koneksi internet, lalu coba lagi.'
    };
}

function stripNomorAntrianFromMessage(message, nomor) {
    var text = String(message || '').trim();
    if (!text) return '';

    text = text
        .replace(/\s*!?\s*Nomor\s+Antrian\s+Anda\s*:\s*[\w-]+/gi, '')
        .replace(/\s*Nomor\s+antrian\s*:\s*[\w-]+/gi, '')
        .replace(/\s{2,}/g, ' ')
        .trim();

    if (text && !/[.!?]$/.test(text)) {
        text += '!';
    }

    return text;
}

function buildPengajuanSuccessHtml(data) {
    var parts = [];
    var serviceName = lmCurrentServiceName || 'Layanan';
    var nomor = data.nomor_antrian || (data.data && data.data.nomor_antrian) || '';
    var rawMessage = data.message || '';
    var detailMessage = stripNomorAntrianFromMessage(rawMessage, nomor);

    if (!detailMessage) {
        detailMessage = 'Permohonan ' + serviceName + ' berhasil dikirim!';
    }

    parts.push('Layanan ' + escapeHtmlText(serviceName) + ' berhasil diajukan.');
    parts.push(escapeHtmlText(detailMessage));

    if (nomor) {
        parts.push('Nomor antrian: ' + escapeHtmlText(nomor));
    }

    return parts.join('<br>');
}

async function submitPengajuanForm(form, btnSubmit) {
    var submissionSucceeded = false;

    if (!form || !form.action || form.action === '#' || form.action.endsWith('#')) {
        toastError(
            'URL pengiriman tidak valid.',
            'Tutup modal, pilih layanan kembali, lalu coba kirim pengajuan.'
        );
        return;
    }

    var formData = new FormData(form);
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta && !formData.get('_token')) {
        formData.set('_token', csrfMeta.content);
    }

    var controller = new AbortController();
    var timeoutId = setTimeout(function() { controller.abort(); }, SUBMIT_TIMEOUT_MS);

    try {
        var response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: controller.signal
        });

        var data = await parseJsonResponse(response);

        if (data.success) {
            submissionSucceeded = true;
            if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
                Swal.close();
            }
            toastSuccess('Pengajuan Berhasil!', buildPengajuanSuccessHtml(data));
            form.reset();
            closeModal();
            lmGoToStep(1);
            document.getElementById('lm_liveness_passed').value = '0';
            document.getElementById('lm_foto_wajah').value = '';
        } else {
            var validationMsg = '';
            if (data.errors) {
                validationMsg = Object.keys(data.errors).map(function(key) {
                    var val = data.errors[key];
                    return Array.isArray(val) ? val.join(' ') : val;
                }).join(' ');
            }
            toastError(
                data.problem || data.message || 'Terjadi kesalahan saat mengirim pengajuan.',
                data.solution || validationMsg || 'Periksa kelengkapan data dan berkas, lalu coba kirim kembali.'
            );
        }
    } catch (error) {
        console.error('Submit error:', error);
        if (error.name === 'AbortError') {
            toastError(
                'Waktu pengiriman habis.',
                'Periksa koneksi internet atau kurangi ukuran berkas, lalu coba lagi.'
            );
        } else {
            toastError(
                'Gagal mengirim pengajuan.',
                'Periksa koneksi internet, lalu coba lagi.'
            );
        }
    } finally {
        clearTimeout(timeoutId);
        if (!submissionSucceeded && typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
            Swal.close();
        }
        if (btnSubmit) btnSubmit.disabled = false;
    }
}

function autoFillFromAntrian(nomorAntrian) {
    if (!nomorAntrian || nomorAntrian.trim().length < 5) return;
    var input = document.getElementById('nomorAntrianInput');
    if (!input) return;
    input.classList.add('loading');

    var layananId = '';
    if (lmCurrentConfig && lmCurrentConfig.fields) {
        var hiddenField = lmCurrentConfig.fields.find(function(f) { return f.name === 'layanan_id'; });
        if (hiddenField) layananId = hiddenField.value || '';
    }

    var apiUrl = '/api/antrian/' + encodeURIComponent(nomorAntrian) + (layananId ? '?layanan_id=' + encodeURIComponent(layananId) : '');

    fetch(apiUrl)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success) {
                var errorCode = data.error_code;
                if (errorCode === 'NOT_FOUND') {
                    toastError(
                        data.problem || data.message || 'Nomor antrian tidak ditemukan dalam sistem.',
                        data.solution || 'Periksa kembali nomor antrian yang diketik, atau buat nomor antrian baru di halaman Antrian Online.',
                        'Nomor antrian tidak ditemukan'
                    );
                    input.value = '';
                } else if (errorCode === 'ALREADY_USED') {
                    toastError(
                        data.problem || data.message || 'Nomor antrian ini sudah digunakan.',
                        data.solution || 'Buat nomor antrian baru di halaman Antrian Online, lalu gunakan nomor baru tersebut.',
                        'Nomor antrian sudah digunakan'
                    );
                    input.value = '';
                } else if (errorCode === 'INVALID_SERVICE') {
                    toastError(
                        data.problem || 'Nomor antrian tidak sesuai dengan layanan yang dipilih.',
                        data.solution || 'Pilih layanan yang sesuai atau buat nomor antrian baru.',
                        'Nomor antrian tidak sesuai layanan',
                        7000
                    );
                    input.value = '';
                } else {
                    toastError(
                        data.problem || data.message || 'Gagal mengambil data antrian.',
                        data.solution || 'Periksa nomor antrian and koneksi internet, lalu coba lagi.',
                        'Gagal mengambil data antrian'
                    );
                }
                return;
            }
            if (data.success && data.data) {
                var nikInput    = document.getElementById('nikPemohonInput');
                var namaInput   = document.getElementById('namaPemohonInput');
                var alamatInput = document.getElementById('alamatPemohonInput')
                    || document.querySelector('[name="alamat_pemohon"]')
                    || document.querySelector('[name="alamat"]');
                if (nikInput    && data.data.nik)          nikInput.value    = data.data.nik;
                if (namaInput   && data.data.nama_lengkap) namaInput.value   = data.data.nama_lengkap;
                if (alamatInput && data.data.alamat)       alamatInput.value = data.data.alamat;
                toastSuccess(
                    'Berhasil Mengambil Data dari Nomor Antrian',
                    'Data pemohon dari nomor antrian <strong>' + escapeHtmlText(nomorAntrian.trim()) + '</strong> telah diisi otomatis ke formulir.'
                );
            }
        })
        .catch(function(error) {
            console.error('autoFillFromAntrian error:', error);
            toastError(
                'Sistem gagal mengambil data antrian.',
                'Periksa koneksi internet, lalu masukkan nomor antrian kembali.',
                'Gagal mengambil data antrian'
            );
        })
        .finally(function() {
            if (input) input.classList.remove('loading');
        });
}

function handleFileSelect(input, fieldName) {
    if (!validateSelectedFile(input)) return;
    var displayDiv = document.getElementById('name-' + fieldName);
    var icon       = document.getElementById('icon-' + fieldName);
    var clearBtn   = document.getElementById('clear-' + fieldName);
    if (input.files && input.files[0]) {
        displayDiv.querySelector('.file-label').textContent = input.files[0].name;
        displayDiv.classList.remove('hidden');
        if (icon) icon.className = 'fas fa-check-circle text-2xl text-green-500 mb-2';
        if (clearBtn) clearBtn.classList.remove('hidden');
    } else {
        displayDiv.classList.add('hidden');
        if (icon) icon.className = 'fas fa-file-pdf text-2xl text-gray-400 mb-2';
        if (clearBtn) clearBtn.classList.add('hidden');
    }
}

function isPdfOnlyInput(input) {
    var accept = (input.getAttribute('accept') || '').toLowerCase();
    return accept.includes('.pdf') && !accept.includes('image') && !accept.includes('.jpg') && !accept.includes('.jpeg') && !accept.includes('.png');
}

function validateSelectedFile(input) {
    var file = input.files && input.files[0] ? input.files[0] : null;
    if (!file) return true;
    if (isPdfOnlyInput(input)) {
        var isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        if (!isPdf) {
            input.value = '';
            toastError('Hanya file PDF yang diperbolehkan.', 'Pilih ulang file dengan format PDF sesuai ketentuan.');
            return false;
        }
    }
    if (file.size > 2 * 1024 * 1024) {
        input.value = '';
        toastError('Maksimal ukuran file: 2MB.', 'Kompres file atau pilih file dengan ukuran di bawah 2MB.');
        return false;
    }
    return true;
}

function clearFileInput(fieldName) {
    var input = document.querySelector('input[type="file"][name="' + fieldName + '"]');
    if (!input) return;
    input.value = '';
    handleFileSelect(input, fieldName);
}

function closeModal() {
    stopCamera();
    resetLiveness();
    document.getElementById('serviceModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

async function handleKirimPengajuan() {
    var livenessInput = document.getElementById('lm_liveness_passed');
    var livenessValue = (livenessInput && livenessInput.value) ? livenessInput.value : '0';

    if (livenessValue !== '1') {
        toastError(
            'Verifikasi wajah belum selesai.',
            'Selesaikan verifikasi wajah pada langkah Verifikasi, lalu kirim pengajuan kembali.'
        );
        lmGoToStep(4);
        return;
    }

    var form           = document.getElementById('lmServiceForm');
    var btnSubmit      = document.getElementById('lmBtnSubmit');
    var nikInput       = document.querySelector('[name="nik_pemohon"]');
    var layananIdInput = document.querySelector('[name="layanan_id"]');
    var nik            = nikInput      ? nikInput.value.trim()      : '';
    var layananId      = layananIdInput ? layananIdInput.value.trim() : '';

    if (btnSubmit) btnSubmit.disabled = true;

    if (nik && layananId) {
        showLoadingModal('Memeriksa...', 'Mohon tunggu sebentar');
        try {
            var controller = new AbortController();
            var timeoutId  = setTimeout(function() { controller.abort(); }, 10000);
            var checkResponse = await fetch(
                '/api/antrian/check-daily-limit?nik=' + encodeURIComponent(nik) + '&layanan_id=' + encodeURIComponent(layananId),
                { signal: controller.signal, headers: { 'Accept': 'application/json' } }
            );
            clearTimeout(timeoutId);
            var checkData = await parseJsonResponse(checkResponse);
            if (!checkData.success) {
                Swal.close();
                if (btnSubmit) btnSubmit.disabled = false;
                toastError(
                    checkData.problem || checkData.message || 'Validasi pengajuan gagal.',
                    checkData.solution || 'Periksa data yang dimasukkan, lalu coba lagi.'
                );
                return;
            }
        } catch (error) {
            Swal.close();
            console.error('Daily limit check error:', error);
        }
    }

    showLoadingModal('Memproses...', 'Mohon tunggu sebentar');
    await submitPengajuanForm(form, btnSubmit);
}
</script>

<script src="{{ asset_v('js/antrian-ocr.js') }}" defer></script>
<script>
 (function watchOcrPanelFriendlyMessage() {
 function applyFriendlyPanelText() {
 var msgEl = document.getElementById('ocrStatusMessage');
 var titleEl = document.getElementById('ocrStatusTitle');
 if (!msgEl || !window.isOcrExtractError || !window.ANTRIAN_OCR_MESSAGES) return;
 if (window.isOcrExtractError(msgEl.textContent)) {
 var copy = window.ANTRIAN_OCR_MESSAGES.extractFail;
 if (titleEl) titleEl.textContent = copy.problem;
 msgEl.textContent = copy.solution;
 }
 }
 document.addEventListener('DOMContentLoaded', function() {
 applyFriendlyPanelText();
 var msgEl = document.getElementById('ocrStatusMessage');
 if (!msgEl) return;
 var observer = new MutationObserver(applyFriendlyPanelText);
 observer.observe(msgEl, { childList: true, characterData: true, subtree: true });
 });
 })();
</script>
<script>
 // Load Statistics on Page Load
 function loadStatistics() {
 fetch('{{ route('antrian.statistik') }}')
 .then(response => response.json())
 .then(data => {
 if (data.success) {
 animateCounter('totalToday', data.data.total_antrian);
 animateCounter('waitingToday', data.data.antrian_menunggu);
 animateCounter('processingToday', data.data.antrian_diproses);
 animateCounter('completedToday', data.data.antrian_selesai);
 }
 })
 .catch(err => console.error('Gagal memuat statistik:', err));
 }

 // Counter Animation
 function animateCounter(elementId, target) {
 const element = document.getElementById(elementId);
 const duration = 1000;
 const steps = 30;
 const increment = target / steps;
 let current = 0;

 const timer = setInterval(() => {
 current += increment;
 if (current >= target) {
 element.textContent = target;
 clearInterval(timer);
 } else {
 element.textContent = Math.floor(current);
 }
 }, duration / steps);
 }

 // Pengiriman form & alur tiket: dihandle oleh public/js/antrian-ocr.js (draft ? OCR ? finalize).

 // Confetti Animation
 function createConfetti() {
 const container = document.getElementById('confetti-container');
 const colors = ['#28A745', '#22c55e', '#36B37E', '#FFAB00', '#FF5630', '#6554C0'];

 for (let i = 0; i < 50; i++) {
 const confetti = document.createElement('div');
 confetti.className = 'confetti';
 confetti.style.left = Math.random() * 100 + 'vw';
 confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
 confetti.style.animationDelay = Math.random() * 2 + 's';
 confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
 container.appendChild(confetti);

 setTimeout(() => confetti.remove(), 4000);
 }
 }

 // Salin Nomor Antrian ke Clipboard
 function copyTicketNumber() {
 const ticketNumber = document.getElementById('ticketNumber').textContent;
 const copyBtn = document.getElementById('copyBtn');
 
 if (!ticketNumber || ticketNumber === '-') {
 toastError(
 'Nomor antrian tidak ditemukan.',
 'Pastikan nomor antrian sudah terbuat sebelum menyalin.'
 );
 return;
 }
 
 navigator.clipboard.writeText(ticketNumber).then(function() {
 const originalText = copyBtn.innerHTML;
 copyBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Tersalin!';
 copyBtn.classList.remove('from-gray-100', 'to-gray-200', 'hover:from-gray-200', 'hover:to-gray-300');
 copyBtn.classList.add('from-green-500', 'to-green-600', 'text-white');
 
 toastSuccess('Berhasil Disalin!', 'Nomor antrian ' + ticketNumber + ' telah disalin');
 
 setTimeout(function() {
 copyBtn.innerHTML = originalText;
 copyBtn.classList.remove('from-green-500', 'to-green-600', 'text-white');
 copyBtn.classList.add('from-gray-100', 'to-gray-200', 'hover:from-gray-200', 'hover:to-gray-300');
 }, 2000);
 }).catch(function() {
 // Fallback
 const textarea = document.createElement('textarea');
 textarea.value = ticketNumber;
 textarea.style.position = 'fixed';
 textarea.style.opacity = '0';
 document.body.appendChild(textarea);
 textarea.select();
 try {
 document.execCommand('copy');
 toastSuccess('Berhasil Disalin!', 'Nomor antrian ' + ticketNumber + ' telah disalin');
 } catch (err) {
 toastError(
 'Tidak dapat menyalin nomor antrian.',
 'Salin nomor secara manual atau coba lagi setelah memberi izin clipboard pada browser.'
 );
 }
 document.body.removeChild(textarea);
 });
 }

 // resetForm: didefinisikan di antrian-ocr.js (konfirmasi Swal + reset multi-step).
 
 // Show Antrian Detail dengan SweetAlert - desain mengikuti modal pernikahan
 window.showAntrianDetail = function(antrian) {
 console.log('Showing antrian detail:', antrian);

 var statusConfigMap = {
 'Menunggu': { hex: '#f59e0b', label: 'Menunggu', icon: 'fa-clock', step: 1 },
 'Dokumen Diterima': { hex: '#3b82f6', label: 'Dokumen Diterima', icon: 'fa-file-check', step: 2 },
 'Verifikasi Data': { hex: '#6366f1', label: 'Verifikasi Data', icon: 'fa-search', step: 3 },
 'Proses Cetak': { hex: '#a855f7', label: 'Proses Cetak', icon: 'fa-print', step: 4 },
 'Selesai': { hex: '#22c55e', label: 'Selesai', icon: 'fa-check-double', step: 5 },
 'Siap Pengambilan': { hex: '#14b8a6', label: 'Proses Cetak', icon: 'fa-print', step: 4 },
 'Berkas Siap Diunduh': { hex: '#22c55e', label: 'Selesai', icon: 'fa-check-double', step: 5 },
 'Ditolak': { hex: '#ef4444', label: 'Ditolak', icon: 'fa-ban', step: 3 },
 'Dibatalkan': { hex: '#f43f5e', label: 'Dibatalkan', icon: 'fa-times', step: 1 }
 };

 var nomorAntrian = antrian.nomor_antrian || '-';
 var namaLengkap = antrian.nama_lengkap || '-';
 var nik = antrian.nik || '-';
 var namaLayanan = (antrian.layanan && antrian.layanan.nama_layanan) ? antrian.layanan.nama_layanan : 'Layanan Umum';
 var statusAntrian = antrian.status_antrian || 'Menunggu';
 var progressInfo = window.resolveAntrianProgress(antrian);
 var isDitolak = progressInfo.isDitolak;
 var isDibatalkan = progressInfo.isDibatalkan;
 var isTerminal = progressInfo.isTerminal;
 var statusConfig = statusConfigMap[statusAntrian] || statusConfigMap['Menunggu'];
 var stepVal = progressInfo.currentStep;
 var stepWidth = progressInfo.stepWidth;
 var progressSubtitleModal = progressInfo.progressSubtitle
 ? '<p class="text-xs text-red-700 mt-1 font-medium text-center">' + progressInfo.progressSubtitle + '</p>'
 : '';

 // Format tanggal pembuatan
 var createdDate = '-';
 if (antrian.created_at) {
 try {
 createdDate = new Date(antrian.created_at).toLocaleString('id-ID', {
 day: 'numeric', month: 'long', year: 'numeric',
 hour: '2-digit', minute: '2-digit'
 });
 } catch (e) { createdDate = antrian.created_at; }
 }

 // Dokumen final: ambil semua lacak_berkas yang punya file/download_url
 var dokumenItems = [];
 if (Array.isArray(antrian.lacak_berkas)) {
 antrian.lacak_berkas.forEach(function(lb) {
 if (lb && lb.download_url) {
 var tgl = '-';
 if (lb.tanggal) {
 try {
 tgl = new Date(lb.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
 } catch (e) { tgl = lb.tanggal; }
 } else if (lb.created_at) {
 try {
 tgl = new Date(lb.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
 } catch (e) { tgl = lb.created_at; }
 }
 dokumenItems.push({
 label: lb.status || 'Dokumen',
 tanggal: tgl,
 url: lb.download_url
 });
 }
 });
 }

 var dokumenHtml = '';
 // Sembunyikan dokumenHtml untuk antrian Ditolak
 if (!isDitolak && dokumenItems.length > 0) {
 var rows = dokumenItems.map(function(it) {
 var viewUrl = it.url + (it.url.indexOf('?') === -1 ? '?' : '&') + 'inline=1';
 return '<div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2 text-xs">' +
 '<div class="flex items-center gap-2">' +
 '<i class="fas fa-file-pdf text-emerald-600"></i>' +
 '<div>' +
 '<p class="text-gray-700 font-semibold">' + it.label + '</p>' +
 '<p class="text-[10px] text-gray-400">' + it.tanggal + '</p>' +
 '</div>' +
 '</div>' +
 '<a href="' + viewUrl + '" target="_blank" rel="noopener" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold shadow-sm inline-flex items-center gap-1 transition">' +
 '<i class="fas fa-eye"></i> Lihat Dokumen' +
 '</a>' +
 '</div>';
 }).join('');

 dokumenHtml =
 '<div class="mb-4">' +
 '<p class="text-xs font-semibold text-gray-700 mb-2"><i class="fas fa-folder-open text-emerald-600 mr-1"></i>Dokumen Hasil Penerbitan Disdukcapil</p>' +
 '<div class="space-y-2">' + rows + '</div>' +
 '</div>';
 }

 var alasanPenolakanModalHtml = '';
 if (isDitolak) {
 var alasanExtractedModal = window.extractAlasanPenolakan(antrian);
 var alasanText = alasanExtractedModal || 'Alasan tidak dicantumkan oleh petugas.';
 var alasanEscaped = String(alasanText)
 .replace(/&/g, '&amp;')
 .replace(/</g, '&lt;')
 .replace(/>/g, '&gt;')
 .replace(/"/g, '&quot;')
 .replace(/'/g, '&#39;');
 var ditolakPadaHtml = progressInfo.failedAtStatus
 ? '<p class="text-xs text-red-600 mb-2"><i class="fas fa-map-marker-alt mr-1"></i>Ditolak pada tahap <strong>' + progressInfo.failedAtStatus + '</strong> (Step ' + progressInfo.failedAtStep + ' dari ' + progressInfo.totalSteps + ')</p>'
 : '';
 alasanPenolakanModalHtml =
 '<div class="mb-4">' +
 '<div class="bg-red-50 border border-red-200 rounded-xl p-4">' +
 '<div class="flex items-start gap-3">' +
 '<i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>' +
 '<div class="flex-1 min-w-0">' +
 ditolakPadaHtml +
 '<p class="text-sm font-semibold text-red-700 mb-1">Alasan Penolakan</p>' +
 '<p class="text-sm text-red-900 break-words">' + alasanEscaped + '</p>' +
 '</div>' +
 '</div>' +
 '</div>' +
 '</div>';
 }

 var statusColorsModal = {
 'Menunggu': { hex: '#f59e0b' },
 'Dokumen Diterima': { hex: '#22c55e' },
 'Verifikasi Data': { hex: '#6366f1' },
 'Proses Cetak': { hex: '#a855f7' },
 'Siap Pengambilan': { hex: '#14b8a6' },
 'Berkas Siap Diunduh': { hex: '#10b981' },
 'Selesai': { hex: '#22c55e' },
 'Ditolak': { hex: '#ef4444' },
 'Tolak': { hex: '#ef4444' },
 'Dibatalkan': { hex: '#f43f5e' }
 };
 var timelineModalHtml = progressInfo.lacakSorted.length > 0
 ? window.buildLacakTimelineHtml(progressInfo.lacakSorted, statusColorsModal)
 : '';

 var modalContent =
 '<div class="p-6">' +
 '<div class="flex items-center justify-between mb-4">' +
 '<div class="flex items-center gap-3">' +
 '<div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white">' +
 '<i class="fas fa-file-alt text-xl"></i>' +
 '</div>' +
 '<div>' +
 '<span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded">ANTRIAN ONLINE</span>' +
 '<h3 class="font-bold text-xl text-gray-800">' + nomorAntrian + '</h3>' +
 '</div>' +
 '</div>' +
 '<button onclick="Swal.close()" class="text-gray-400 hover:text-gray-600">' +
 '<i class="fas fa-times text-xl"></i>' +
 '</button>' +
 '</div>' +

 '<div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 mb-4">' +
 '<div class="flex items-center gap-3">' +
 '<div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-2xl" style="background-color: ' + statusConfig.hex + '">' +
 '<i class="fas ' + statusConfig.icon + '"></i>' +
 '</div>' +
 '<div>' +
 '<p class="font-bold text-lg" style="color: ' + statusConfig.hex + '">' + statusConfig.label + '</p>' +
 '<p class="text-xs text-gray-500">Status saat ini</p>' +
 '</div>' +
 '</div>' +
 '</div>' +

 '<div class="mb-4">' +
 '<div class="flex justify-between text-xs text-gray-500 mb-1">' +
 '<span>Progress Pengajuan</span>' +
 '<span>' + progressInfo.progressLabel + '</span>' +
 '</div>' +
 '<div class="w-full bg-gray-200 rounded-full h-3 relative overflow-hidden">' +
 '<div class="bg-gradient-to-r ' + (isDitolak ? 'from-red-500 to-rose-600' : (isDibatalkan ? 'from-rose-400 to-rose-600' : 'from-green-500 to-emerald-500')) + ' h-3 rounded-full transition-all" style="width: ' + stepWidth + '%"></div>' +
 (isTerminal ? '<div class="absolute top-0 h-3 w-1 bg-red-800 rounded-full" style="left: calc(' + stepWidth + '% - 2px)"></div>' : '') +
 '</div>' +
 progressSubtitleModal +
 '</div>' +

 '<div class="bg-gray-50 rounded-xl p-4 space-y-3 mb-4">' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">Nama Pemohon</span>' +
 '<span class="font-semibold text-gray-800 text-sm">' + namaLengkap + '</span>' +
 '</div>' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">NIK</span>' +
 '<span class="font-mono text-gray-800 text-sm">' + nik + '</span>' +
 '</div>' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">Layanan</span>' +
 '<span class="font-semibold text-gray-800 text-sm">' + namaLayanan + '</span>' +
 '</div>' +
 '<div class="flex justify-between">' +
 '<span class="text-xs text-gray-500">Tanggal Pengajuan</span>' +
 '<span class="font-semibold text-gray-800 text-sm">' + createdDate + '</span>' +
 '</div>' +
 '</div>' +

 alasanPenolakanModalHtml +

 timelineModalHtml +

 dokumenHtml +

 '<div class="flex gap-2">' +
 '<button onclick="window.copyNomorAntrianToClipboard(\'' + nomorAntrian + '\'); Swal.close();" class="flex-1 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700">' +
 '<i class="fas fa-copy mr-1"></i> Salin Nomor' +
 '</button>' +
 '<button onclick="Swal.close()" class="flex-1 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-300">' +
 'Tutup' +
 '</button>' +
 '</div>' +
 '</div>';

 Swal.fire({
 html: modalContent,
 showConfirmButton: false,
 width: '500px',
 customClass: { popup: 'rounded-2xl' }
 });
 }

 // Copy Nomor Antrian to Clipboard
 window.copyNomorAntrianToClipboard = function(text) {
 if (navigator.clipboard && navigator.clipboard.writeText) {
 navigator.clipboard.writeText(text).then(() => {
 toastSuccess('Berhasil Disalin!', 'Nomor antrian ' + text + ' telah disalin');
 }).catch(() => {
 fallbackCopyNomor(text);
 });
 } else {
 fallbackCopyNomor(text);
 }
 }

 function fallbackCopyNomor(text) {
 const textarea = document.createElement('textarea');
 textarea.value = text;
 textarea.style.position = 'fixed';
 textarea.style.opacity = '0';
 document.body.appendChild(textarea);
 textarea.select();
 try {
 document.execCommand('copy');
 toastSuccess('Berhasil Disalin!', 'Nomor antrian ' + text + ' telah disalin');
 } catch (err) {
 toastError(
 'Tidak dapat menyalin nomor antrian.',
 'Salin nomor secara manual atau coba lagi setelah memberi izin clipboard pada browser.'
 );
 }
 document.body.removeChild(textarea);
 }
 
 // Enter key support for search
 document.addEventListener('DOMContentLoaded', function() {
 var searchInput = document.getElementById('searchInput');
 if (searchInput) {
 searchInput.addEventListener('keypress', function(e) {
 if (e.key === 'Enter') {
 e.preventDefault();
 searchAntrian();
 }
 });
 }

 // Button click handler untuk Cari Antrian
 var btnCari = document.getElementById('btnCariAntrian');
 if (btnCari) {
 btnCari.addEventListener('click', function(e) {
 e.preventDefault();
 e.stopPropagation();
 console.log('Button Cari Antrian clicked (addEventListener)');
 searchAntrian();
 });
 console.log('Button event listener attached to btnCariAntrian');
 } else {
 console.error('Button btnCariAntrian not found');
 }

 // Load statistics on page load
 loadStatistics();

 // Debug: Check if searchAntrian function exists
 console.log('searchAntrian function exists:', typeof searchAntrian === 'function');
 });

 // Global error handler untuk debugging
 window.addEventListener('error', function(e) {
 console.error('Global error:', e.message, 'at', e.filename, 'line', e.lineno);
 });
</script>
@endpush
