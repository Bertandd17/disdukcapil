<?php

namespace App\Http\Controllers;

use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use App\Models\KartuKeluarga;
use App\Models\GantiKepalaKK;
use App\Models\KKHilangRusak;
use App\Models\PisahKK;
use App\Models\Antrian_Online_Model;
use App\Models\Lacak_Berkas_Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KartKeluargaController extends Controller
{
    // Untuk User Mengurus KK Ganti Data 
    public function store_perubahan_data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|string|exists:layanan,layanan_id',
            'nomor_antrian' => 'required|string|unique:ganti_data_kk,nomor_antrian',
            'nama_pemohon' => 'required|string',
            'nik_pemohon' => 'required|digits:16',
            'nomor_kk_pemohon' => 'required|integer',
            'alamat_pemohon' => 'required|string',
            'formulir_f102' => 'required|file|mimes:pdf|max:2048',
            'ktp_pemohon' => 'required|file|mimes:pdf|max:2048',
            'kk_pemohon' => 'required|file|mimes:pdf|max:2048',
            'formulir_f106' => 'required|file|mimes:pdf|max:2048',
            'surat_keterangan_perubahan' => 'required|file|mimes:pdf|max:2048',
            'pernyataan_pindah_kk' => 'nullable|file|mimes:pdf|max:2048',
            'status' => 'nullable|string'
        ],[
            'layanan_id.required' => 'ID layanan tidak boleh kosong.',
            'nomor_antrian.required' => 'Nomor antrian tidak boleh kosong.',
            'nomor_antrian.unique' => 'Nomor antrian ini sudah terdaftar di sistem.',
            'nama_pemohon.required' => 'Nama pemohon tidak boleh kosong.',
            'nik_pemohon.required' => 'NIK pemohon tidak boleh kosong.',
            'nik_pemohon.digits' => 'NIK pemohon harus terdiri dari 16 digit.',
            'nomor_kk_pemohon.required' => 'Nomor KK pemohon tidak boleh kosong.',
            'nomor_kk_pemohon.integer' => 'Nomor KK pemohon harus berupa angka.',
            'alamat_pemohon.required' => 'Alamat pemohon tidak boleh kosong.',
            'formulir_f102.required' => 'Formulir F102 tidak boleh kosong.',
            'formulir_f102.file' => 'Formulir F102 harus berupa file.',
            'formulir_f102.mimes' => 'Formulir F102 harus berformat PDF.',
            'formulir_f102.max' => 'Ukuran Formulir F102 tidak boleh lebih dari 5 MB.',
            'ktp_pemohon.required' => 'KTP pemohon tidak boleh kosong.',
            'ktp_pemohon.file' => 'KTP pemohon harus berupa file.',
            'ktp_pemohon.mimes' => 'KTP pemohon harus berformat PDF.',
            'ktp_pemohon.max' => 'Ukuran KTP pemohon tidak boleh lebih dari 5 MB.',
            'kk_pemohon.required' => 'KK pemohon tidak boleh kosong.',
            'kk_pemohon.file' => 'KK pemohon harus berupa file.',
            'kk_pemohon.mimes' => 'KK pemohon harus berformat PDF.',
            'kk_pemohon.max' => 'Ukuran KK pemohon tidak boleh lebih dari 5 MB.',
            'formulir_f106.required' => 'Formulir F106 tidak boleh kosong.',
            'formulir_f106.file' => 'Formulir F106 harus berupa file.',
            'formulir_f106.mimes' => 'Formulir F106 harus berformat PDF.',
            'formulir_f106.max' => 'Ukuran Formulir F106 tidak boleh lebih dari 5 MB.',
            'surat_keterangan_perubahan.required' => 'Surat keterangan perubahan tidak boleh kosong.',
            'surat_keterangan_perubahan.file' => 'Surat keterangan perubahan harus berupa file.',
            'surat_keterangan_perubahan.mimes' => 'Surat keterangan perubahan harus berformat PDF.',
            'surat_keterangan_perubahan.max' => 'Ukuran surat keterangan perubahan tidak boleh lebih dari 5 MB.',
            'pernyataan_pindah_kk.file' => 'Pernyataan pindah KK harus berupa file.',
            'pernyataan_pindah_kk.mimes' => 'Pernyataan pindah KK harus berformat PDF.',
            'pernyataan_pindah_kk.max' => 'Ukuran pernyataan pindah KK tidak boleh lebih dari 5 MB.',
        ]);
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $antrian = $this->validatedAntrianForLayanan($request);
        if (!$antrian instanceof Antrian_Online_Model) {
            return $antrian;
        }
        $data = $request->except([
            'formulir_f102', 'ktp_pemohon','kk_pemohon','formulir_f106','surat_keterangan_perubahan', 'pernyataan_pindah_kk','foto_wajah'
        ]);
        $data['nomor_antrian'] = $antrian->nomor_antrian;
        $data['status'] = 'Verifikasi Data';
        $fileFields = [
            'formulir_f102', 
            'ktp_pemohon', 
            'kk_pemohon', 
            'formulir_f106',
            'surat_keterangan_perubahan', 
            'pernyataan_pindah_kk'
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('ubah_data_kk', 'private');
            }
        }
        if ($request->filled('foto_wajah')) {
            $base64   = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_wajah);
            $decoded  = base64_decode($base64);
            $filename = 'wajah_' . uniqid() . '_' . time() . '.jpg';
            Storage::disk('private')->put("ubah_data_kk/{$filename}", $decoded);
            $data['foto_wajah'] = "ubah_data_kk/{$filename}";
        }
        KartuKeluarga::create($data);
        $antrian->update(['status_antrian' => 'Verifikasi Data']);

        // Kirim notifikasi ke admin
        AdminNotificationService::layananKKBaru(
            $data['nama_pemohon'],
            'Perubahan Data',
            $data['nomor_antrian']
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data dan dokumen berhasil dikirim.']);
        }
        return redirect()->route('layanan-mandiri')
                        ->with('success', 'Data dan dokumen berhasil dikirim.');
    }
    // Untuk user yang berganti kepala keluarga
    public function store_ganti_kepala_kk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|string|exists:layanan,layanan_id',
            'nomor_antrian' => 'required|string',
            'nama_pemohon' => 'required|string',
            'nik_pemohon' => 'required|digits:16',
            'nomor_kk_pemohon' => 'required|integer',
            'alamat_pemohon' => 'required|string',
            'formulir_f102' => 'required|file|mimes:pdf|max:2048',
            'ktp_pemohon' => 'required|file|mimes:pdf|max:2048',
            'kk_pemohon' => 'required|file|mimes:pdf|max:2048',
            'akta_kematian' => 'required|file|mimes:pdf|max:2048',
            'surat_pernyataan_wali' => 'nullable|file|mimes:pdf|max:2048',
            'status' => 'nullable|string'
        ], [
            'layanan_id.required' => 'ID layanan tidak boleh kosong.',
            'nomor_antrian.required' => 'Nomor antrian tidak boleh kosong.',
            'nama_pemohon.required' => 'Nama pemohon tidak boleh kosong.',
            'nik_pemohon.required' => 'NIK pemohon tidak boleh kosong.',
            'nik_pemohon.digits' => 'NIK pemohon harus terdiri dari 16 digit.',
            'nomor_kk_pemohon.required' => 'Nomor KK pemohon tidak boleh kosong.',
            'nomor_kk_pemohon.integer' => 'Nomor KK pemohon harus berupa angka.',
            'alamat_pemohon.required' => 'Alamat pemohon tidak boleh kosong.',
            'formulir_f102.required' => 'Formulir F102 tidak boleh kosong.',
            'formulir_f102.file' => 'Formulir F102 harus berupa file.',
            'formulir_f102.mimes' => 'Formulir F102 harus berformat PDF.',
            'formulir_f102.max' => 'Ukuran Formulir F102 tidak boleh lebih dari 5 MB.',
            'ktp_pemohon.required' => 'KTP pemohon tidak boleh kosong.',
            'ktp_pemohon.file' => 'KTP pemohon harus berupa file.',
            'ktp_pemohon.mimes' => 'KTP pemohon harus berformat PDF.',
            'ktp_pemohon.max' => 'Ukuran KTP pemohon tidak boleh lebih dari 5 MB.',
            'kk_pemohon.required' => 'KK pemohon tidak boleh kosong.',
            'kk_pemohon.file' => 'KK pemohon harus berupa file.',
            'kk_pemohon.mimes' => 'KK pemohon harus berformat PDF.',
            'kk_pemohon.max' => 'Ukuran KK pemohon tidak boleh lebih dari 5 MB.',
            'akta_kematian.required' => 'Akta kematian tidak boleh kosong.',
            'akta_kematian.file' => 'Akta kematian harus berupa file.',
            'akta_kematian.mimes' => 'Akta kematian harus berformat PDF.',
            'akta_kematian.max' => 'Ukuran akta kematian tidak boleh lebih dari 5 MB.',
            'surat_pernyataan_wali.file' => 'Surat pernyataan wali harus berupa file.',
            'surat_pernyataan_wali.mimes' => 'Surat pernyataan wali harus berformat PDF.',
            'surat_pernyataan_wali.max' => 'Ukuran surat pernyataan wali tidak boleh lebih dari 5 MB.',
        ]);
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $antrian = $this->validatedAntrianForLayanan($request);
        if (!$antrian instanceof Antrian_Online_Model) {
            return $antrian;
        }
        $data = $request->except([
            'formulir_f102', 'ktp_pemohon','kk_pemohon','akta_kematian','surat_pernyataan_wali','foto_wajah'
        ]);
        $data['nomor_antrian'] = $antrian->nomor_antrian;
        $data['status'] = 'Verifikasi Data';
        $fileFields = [
            'formulir_f102', 
            'ktp_pemohon', 
            'kk_pemohon', 
            'akta_kematian',
            'surat_pernyataan_wali'
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('ganti_kepala_kk', 'private');
            }
        }
        if ($request->filled('foto_wajah')) {
            $base64   = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_wajah);
            $decoded  = base64_decode($base64);
            $filename = 'wajah_' . uniqid() . '_' . time() . '.jpg';
            Storage::disk('private')->put("ganti_kepala_kk/{$filename}", $decoded);
            $data['foto_wajah'] = "ganti_kepala_kk/{$filename}";
        }
        GantiKepalaKK::create($data);
        $antrian->update(['status_antrian' => 'Verifikasi Data']);

        // Kirim notifikasi ke admin
        AdminNotificationService::layananKKBaru(
            $data['nama_pemohon'],
            'Ganti Kepala',
            $data['nomor_antrian']
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data dan dokumen berhasil dikirim.']);
        }
        return redirect()->route('layanan-mandiri')
                        ->with('success', 'Data dan dokumen berhasil dikirim.');
    }

    // Untuk user yang berganti kepala keluarga
    public function store_kk_hilang_rusak(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|string|exists:layanan,layanan_id',
            'nomor_antrian' => 'required|string|unique:kk_hilang_rusak,nomor_antrian',
            'nama_pemohon' => 'required|string',
            'nik_pemohon' => 'required|digits:16',
            'nomor_kk_pemohon' => 'required|integer',
            'alamat_pemohon' => 'required|string',
            'formulir_f102' => 'required|file|mimes:pdf|max:2048',
            'ktp_pemohon' => 'required|file|mimes:pdf|max:2048',
            'suket_hilang_rusak' => 'required|file|mimes:pdf|max:2048',
            'status' => 'nullable|string'
        ], [
            'layanan_id.required' => 'ID layanan tidak boleh kosong.',
            'nomor_antrian.required' => 'Nomor antrian tidak boleh kosong.',
            'nomor_antrian.unique' => 'Nomor antrian ini sudah terdaftar di sistem kami.',
            'nama_pemohon.required' => 'Nama pemohon tidak boleh kosong.',
            'nik_pemohon.required' => 'NIK pemohon tidak boleh kosong.',
            'nik_pemohon.digits' => 'NIK pemohon harus terdiri dari 16 digit.',
            'nomor_kk_pemohon.required' => 'Nomor KK pemohon tidak boleh kosong.',
            'nomor_kk_pemohon.integer' => 'Nomor KK pemohon harus berupa angka.',
            'alamat_pemohon.required' => 'Alamat pemohon tidak boleh kosong.',
            'formulir_f102.required' => 'Formulir F102 tidak boleh kosong.',
            'formulir_f102.file' => 'Formulir F102 harus berupa file.',
            'formulir_f102.mimes' => 'Formulir F102 harus berformat PDF.',
            'formulir_f102.max' => 'Ukuran Formulir F102 tidak boleh lebih dari 5 MB.',
            'ktp_pemohon.required' => 'KTP pemohon tidak boleh kosong.',
            'ktp_pemohon.file' => 'KTP pemohon harus berupa file.',
            'ktp_pemohon.mimes' => 'KTP pemohon harus berformat PDF.',
            'ktp_pemohon.max' => 'Ukuran KTP pemohon tidak boleh lebih dari 5 MB.',
            'suket_hilang_rusak.required' => 'Surat keterangan hilang/rusak tidak boleh kosong.',
            'suket_hilang_rusak.file' => 'Surat keterangan hilang/rusak harus berupa file.',
            'suket_hilang_rusak.mimes' => 'Surat keterangan hilang/rusak harus berformat PDF.',
            'suket_hilang_rusak.max' => 'Ukuran surat keterangan hilang/rusak tidak boleh lebih dari 5 MB.',
        ]);
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $antrian = $this->validatedAntrianForLayanan($request);
        if (!$antrian instanceof Antrian_Online_Model) {
            return $antrian;
        }
        $data = $request->except([
            'formulir_f102','ktp_pemohon','suket_hilang_rusak','foto_wajah'
        ]);
        $data['nomor_antrian'] = $antrian->nomor_antrian;
        $data['status'] = 'Verifikasi Data';
        $fileFields = [
            'formulir_f102', 
            'ktp_pemohon', 
            'kk_pemohon',
            'suket_hilang_rusak', 
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('kk_hilang_rusak', 'private');
            }
        }
        if ($request->filled('foto_wajah')) {
            $base64   = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_wajah);
            $decoded  = base64_decode($base64);
            $filename = 'wajah_' . uniqid() . '_' . time() . '.jpg';
            Storage::disk('private')->put("kk_hilang_rusak/{$filename}", $decoded);
            $data['foto_wajah'] = "kk_hilang_rusak/{$filename}";
        }
        KKHilangRusak::create($data);
        $antrian->update(['status_antrian' => 'Verifikasi Data']);

        // Kirim notifikasi ke admin
        AdminNotificationService::layananKKBaru(
            $data['nama_pemohon'],
            'Hilang Rusak',
            $data['nomor_antrian']
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data dan dokumen berhasil dikirim.']);
        }
        return redirect()->route('layanan-mandiri')
                        ->with('success', 'Data dan dokumen berhasil dikirim.');
    }

    public function store_pisah_kk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|string|exists:layanan,layanan_id',
            'nomor_antrian' => 'required|string',
            'nama_pemohon' => 'required|string',
            'nik_pemohon' => 'required|digits:16',
            'nomor_kk_pemohon' => 'required|integer',
            'alamat_pemohon' => 'required|string',
            'formulir_f102' => 'required|file|mimes:pdf|max:2048',
            'ktp_pemohon' => 'required|file|mimes:pdf|max:2048',
            'kk_pemohon' => 'required|file|mimes:pdf|max:2048',
            'fotokopi_buku_nikah' => 'nullable|file|mimes:pdf|max:2048',
            'kk_lama' => 'required|file|mimes:pdf|max:2048',
            'status' => 'nullable|string'
        ], [
            'layanan_id.required' => 'ID layanan tidak boleh kosong.',
            'nomor_antrian.required' => 'Nomor antrian tidak boleh kosong.',
            'nama_pemohon.required' => 'Nama pemohon tidak boleh kosong.',
            'nik_pemohon.required' => 'NIK pemohon tidak boleh kosong.',
            'nik_pemohon.digits' => 'NIK pemohon harus terdiri dari 16 digit.',
            'nomor_kk_pemohon.required' => 'Nomor KK pemohon tidak boleh kosong.',
            'nomor_kk_pemohon.integer' => 'Nomor KK pemohon harus berupa angka.',
            'alamat_pemohon.required' => 'Alamat pemohon tidak boleh kosong.',
            'formulir_f102.required' => 'Formulir F102 tidak boleh kosong.',
            'formulir_f102.file' => 'Formulir F102 harus berupa file.',
            'formulir_f102.mimes' => 'Formulir F102 harus berformat PDF.',
            'formulir_f102.max' => 'Ukuran Formulir F102 tidak boleh lebih dari 5 MB.',
            'ktp_pemohon.required' => 'KTP pemohon tidak boleh kosong.',
            'ktp_pemohon.file' => 'KTP pemohon harus berupa file.',
            'ktp_pemohon.mimes' => 'KTP pemohon harus berformat PDF.',
            'ktp_pemohon.max' => 'Ukuran KTP pemohon tidak boleh lebih dari 5 MB.',
            'kk_pemohon.required' => 'KK pemohon tidak boleh kosong.',
            'kk_pemohon.file' => 'KK pemohon harus berupa file.',
            'kk_pemohon.mimes' => 'KK pemohon harus berformat PDF.',
            'kk_pemohon.max' => 'Ukuran KK pemohon tidak boleh lebih dari 5 MB.',
            'fotokopi_buku_nikah.file' => 'Fotokopi buku nikah harus berupa file.',
            'fotokopi_buku_nikah.mimes' => 'Fotokopi buku nikah harus berformat PDF.',
            'fotokopi_buku_nikah.max' => 'Ukuran fotokopi buku nikah tidak boleh lebih dari 5 MB.',
            'kk_lama.required' => 'KK lama tidak boleh kosong.',
            'kk_lama.file' => 'KK lama harus berupa file.',
            'kk_lama.mimes' => 'KK lama harus berformat PDF.',
            'kk_lama.max' => 'Ukuran KK lama tidak boleh lebih dari 5 MB.',
        ]);
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }
        $antrian = $this->validatedAntrianForLayanan($request);
        if (!$antrian instanceof Antrian_Online_Model) {
            return $antrian;
        }
        $data = $request->except([
            'formulir_f102','ktp_pemohon','kk_pemohon','fotokopi_buku_nikah', 'kk_lama','foto_wajah'
        ]);
        $data['nomor_antrian'] = $antrian->nomor_antrian;
        $data['status'] = 'Verifikasi Data';
        $fileFields = [
            'formulir_f102', 
            'ktp_pemohon',
            'kk_pemohon', 
            'fotokopi_buku_nikah',
            'kk_lama',
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('pisah_kk', 'private');
            }
        }
        if ($request->filled('foto_wajah')) {
            $base64   = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_wajah);
            $decoded  = base64_decode($base64);
            $filename = 'wajah_' . uniqid() . '_' . time() . '.jpg';
            Storage::disk('private')->put("pisah_kk/{$filename}", $decoded);
            $data['foto_wajah'] = "pisah_kk/{$filename}";
        }
        PisahKK::create($data);
        $antrian->update(['status_antrian' => 'Verifikasi Data']);

        // Kirim notifikasi ke admin
        AdminNotificationService::layananKKBaru(
            $data['nama_pemohon'],
            'Pisah KK',
            $data['nomor_antrian']
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data dan dokumen berhasil dikirim.']);
        }
        return redirect()->route('layanan-mandiri')
                        ->with('success', 'Data dan dokumen berhasil dikirim.');
    }

    private function validatedAntrianForLayanan(Request $request)
    {
        $antrian = Antrian_Online_Model::with('layanan')
            ->cariNomorExact($request->nomor_antrian)
            ->first();

        if (!$antrian) {
            return $this->invalidAntrianResponse($request, 'Nomor antrian tidak ditemukan dalam sistem.');
        }

        $validasiLayanan = $antrian->validateForLayanan($request->layanan_id);
        if (!$validasiLayanan['valid']) {
            return $this->invalidAntrianResponse($request, strip_tags($validasiLayanan['message']));
        }

        return $antrian;
    }

    private function invalidAntrianResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->with('error', $message)->withInput();
    }


    // Admin
    public function daftar_kk(Request $request)
    {
        // Hanya tampilkan data yang antriannya sudah dimulai admin (status_antrian != 'Menunggu')
        $startedNomorAntrian = \DB::table('antrian_online')
            ->where('status_antrian', '!=', 'Menunggu')
            ->pluck('nomor_antrian');

        $kk1 = KartuKeluarga::select('uuid','nama_pemohon','nomor_antrian','status')
            ->whereIn('nomor_antrian', $startedNomorAntrian)
            ->addSelect(\DB::raw("'Perubahan Data' as jenis"));

        $kk2 = GantiKepalaKK::select('uuid','nama_pemohon','nomor_antrian','status')
            ->whereIn('nomor_antrian', $startedNomorAntrian)
            ->addSelect(\DB::raw("'Ganti Kepala' as jenis"));

        $kk3 = KKHilangRusak::select('uuid','nama_pemohon','nomor_antrian','status')
            ->whereIn('nomor_antrian', $startedNomorAntrian)
            ->addSelect(\DB::raw("'Hilang Rusak' as jenis"));

        $kk4 = PisahKK::select('uuid','nama_pemohon','nomor_antrian','status')
            ->whereIn('nomor_antrian', $startedNomorAntrian)
            ->addSelect(\DB::raw("'Pisah KK' as jenis"));
        $query = $kk1
            ->unionAll($kk2)
            ->unionAll($kk3)
            ->unionAll($kk4);
        $datakk = \DB::table(\DB::raw("({$query->toSql()}) as kk"))
            ->mergeBindings($query->getQuery())
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->get();

        // Statistik
        $jumlahkk = $datakk->count();
        $menungguVerifikasi = $datakk->where('status','Verifikasi Data')->count();
        $dalamProses = $datakk->where('status','Proses Cetak')->count();
        $selesai = $datakk->where('status','Siap Pengambilan')->count();

        return view('admin.penerbitan_kk', compact(
            'datakk','jumlahkk','menungguVerifikasi','dalamProses','selesai'
        ));
    }

    public function detail(Request $request, $uuid, $jenis)
    {
        switch ($jenis) {
            case 'Perubahan Data':
                $berkas = KartuKeluarga::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Ganti Kepala':
                $berkas = GantiKepalaKK::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Hilang Rusak':
                $berkas = KKHilangRusak::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Pisah KK':
                $berkas = PisahKK::where('uuid', $uuid)->firstOrFail();
                break;
            default:
                abort(404);
        }
        return view('admin.penerbitan_kk_detail', compact('berkas','jenis'));
    }

    public function updateStatus(Request $request, $uuid, $jenis)
    {
        switch ($jenis) {
            case 'Perubahan Data':
                $kk = KartuKeluarga::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Ganti Kepala':
                $kk = GantiKepalaKK::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Hilang Rusak':
                $kk = KKHilangRusak::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Pisah KK':
                $kk = PisahKK::where('uuid', $uuid)->firstOrFail();
                break;
            default:
                abort(404);
        }
        $kk->status = $request->status;
        if ($request->status == 'Tolak') {
            $kk->alasan_penolakan = $request->alasan;
        } else {
            $kk->alasan_penolakan = null;
        }
        $kk->save();

        $antrianId = $kk->antrian_online_id ?? Antrian_Online_Model::where('nomor_antrian', $kk->nomor_antrian)->value('antrian_online_id');
        if ($antrianId) {
            $statusAntrian = $request->status === 'Tolak' ? 'Ditolak' : $request->status;
            Antrian_Online_Model::where('antrian_online_id', $antrianId)->update(['status_antrian' => $statusAntrian]);
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrianId,
                'status'            => $request->status,
                'tanggal'           => now()->toDateString(),
                'keterangan'        => $request->status === 'Tolak'
                    ? 'Permohonan ditolak. Alasan: ' . ($request->alasan ?? '-')
                    : 'Status diperbarui menjadi ' . $request->status . '.',
            ]);
        }

        return redirect()->back()->with('success','Status berhasil diperbarui');
    }

    public function lihatBerkas($uuid, $jenis, $field)
    {
        switch ($jenis) {
            case 'Perubahan Data':
                $berkas = KartuKeluarga::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Ganti Kepala':
                $berkas = GantiKepalaKK::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Hilang Rusak':
                $berkas = KKHilangRusak::where('uuid', $uuid)->firstOrFail();
                break;
            case 'Pisah KK':
                $berkas = PisahKK::where('uuid', $uuid)->firstOrFail();
                break;
            default:
                abort(404);
        }
        $path = $berkas->$field;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }
        return Storage::disk('private')->response($path);
    }

    public function uploadBerkasFinal(Request $request, $uuid, $jenis)
    {
        $validator = Validator::make($request->all(), [
            'file_berkas' => 'required|file|mimes:pdf|max:2048',
        ], [
            'file_berkas.required' => 'File berkas wajib diunggah.',
            'file_berkas.mimes'    => 'Format yang diizinkan: PDF.',
            'file_berkas.max'      => 'Ukuran file maksimal 2 MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with('upload_error', $validator->errors()->first());
        }

        switch ($jenis) {
            case 'Perubahan Data':
                $kk = KartuKeluarga::where('uuid', $uuid)->firstOrFail();
                $slug = 'kk-perubahan-data';
                break;
            case 'Ganti Kepala':
                $kk = GantiKepalaKK::where('uuid', $uuid)->firstOrFail();
                $slug = 'kk-ganti-kepala';
                break;
            case 'Hilang Rusak':
                $kk = KKHilangRusak::where('uuid', $uuid)->firstOrFail();
                $slug = 'kk-hilang-rusak';
                break;
            case 'Pisah KK':
                $kk = PisahKK::where('uuid', $uuid)->firstOrFail();
                $slug = 'kk-pisah';
                break;
            default:
                abort(404);
        }

        $file     = $request->file('file_berkas');
        $ext      = $file->getClientOriginalExtension();
        $filename = $slug . '-' . \Illuminate\Support\Str::slug($kk->nama_pemohon ?? 'pemohon') . '-' . time() . '.' . $ext;
        $path     = $file->storeAs('berkas-final/' . $slug, $filename, 'private');

        $antrianId = $kk->antrian_online_id ?? Antrian_Online_Model::where('nomor_antrian', $kk->nomor_antrian)->value('antrian_online_id');
        if ($antrianId) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrianId,
                'status'            => 'Berkas Siap Diunduh',
                'tanggal'           => now()->toDateString(),
                'keterangan'        => 'Berkas Kartu Keluarga (' . $jenis . ') telah diunggah oleh admin. Silakan unduh.',
                'file_berkas'       => $path,
            ]);
            Antrian_Online_Model::where('antrian_online_id', $antrianId)->update(['status_antrian' => 'Selesai']);
        }

        $kk->update(['status' => 'Selesai']);

        return redirect()->back()->with('success', 'Berkas berhasil diunggah dan dapat diunduh oleh pemohon.');
    }
}
