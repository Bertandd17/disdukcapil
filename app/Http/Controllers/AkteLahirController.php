<?php

namespace App\Http\Controllers;

use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use App\Models\AkteLahir;
use App\Models\Antrian_Online_Model;
use App\Models\Lacak_Berkas_Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AkteLahirController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|exists:layanan,layanan_id',
            'nomor_antrian' => 'required|string',
            'nama_pemohon' => 'required|string',
            'nik_pemohon' => 'required|digits:16',
            'nomor_kk_pemohon' => 'required|string',
            'alamat' => 'required|string',
            'formulir_f201' => 'required|file|mimes:pdf|max:2048',
            'ktp_pemohon' => 'required|file|mimes:pdf|max:2048',
            'ktp_saksi1' => 'required|file|mimes:pdf|max:2048',
            'ktp_saksi2' => 'required|file|mimes:pdf|max:2048',
            'kk_pemohon' => 'required|file|mimes:pdf|max:2048',
            'file_surat_lahir' => 'required|file|mimes:pdf|max:2048',
            'file_buku_nikah' => 'required|file|mimes:pdf|max:2048',
            'file_sptjm_kelahiran' => 'nullable|file|mimes:pdf|max:2048',
            'file_sptjm_pasutri' => 'nullable|file|mimes:pdf|max:2048',
            'file_berita_acara_polisi' => 'nullable|file|mimes:pdf|max:2048',
        ], [
            'layanan_id.required' => 'ID layanan tidak boleh kosong.',
            'layanan_id.exists' => 'ID layanan tidak ditemukan di sistem.',
            'nomor_antrian.required' => 'Nomor antrian tidak boleh kosong.',
            'nomor_antrian.string' => 'Nomor antrian harus berupa teks.',
            'nama_pemohon.required' => 'Nama pemohon tidak boleh kosong.',
            'nama_pemohon.string' => 'Nama pemohon harus berupa teks.',
            'nik_pemohon.required' => 'NIK pemohon tidak boleh kosong.',
            'nik_pemohon.digits' => 'NIK pemohon harus terdiri dari 16 digit.',
            'nomor_kk_pemohon.required' => 'Nomor KK pemohon tidak boleh kosong.',
            'nomor_kk_pemohon.string' => 'Nomor KK pemohon harus berupa teks.',
            'alamat.required' => 'Alamat tidak boleh kosong.',
            'alamat.string' => 'Alamat harus berupa teks.',
            'formulir_f201.required' => 'Formulir F201 tidak boleh kosong.',
            'formulir_f201.file' => 'Formulir F201 harus berupa file.',
            'formulir_f201.mimes' => 'Formulir F201 harus berformat PDF.',
            'formulir_f201.max' => 'Ukuran Formulit F201 tidak boleh lebih dari 500 KB.',
            'ktp_pemohon.required' => 'KTP pemohon tidak boleh kosong.',
            'ktp_pemohon.file' => 'KTP pemohon harus berupa file.',
            'ktp_pemohon.mimes' => 'KTP pemohon harus berformat PDF.',
            'ktp_pemohon.max' => 'Ukuran KTP pemohon tidak boleh lebih dari 500 KB.',
            'ktp_saksi1.required' => 'KTP saksi 1 tidak boleh kosong.',
            'ktp_saksi1.file' => 'KTP saksi 1 harus berupa file.',
            'ktp_saksi1.mimes' => 'KTP saksi 1 harus berformat PDF.',
            'ktp_saksi1.max' => 'Ukuran KTP saksi tidak boleh lebih dari 500 KB.',
            'ktp_saksi2.required' => 'KTP saksi 2 tidak boleh kosong.',
            'ktp_saksi2.file' => 'KTP saksi 2 harus berupa file.',
            'ktp_saksi2.mimes' => 'KTP saksi 2 harus berformat PDF.',
            'ktp_saksi2.max' => 'Ukuran KTP saksi2 tidak boleh lebih dari 500 KB.',
            'kk_pemohon.required' => 'KK pemohon tidak boleh kosong.',
            'kk_pemohon.file' => 'KK pemohon harus berupa file.',
            'kk_pemohon.mimes' => 'KK pemohon harus berformat PDF.',
            'kk_pemohon.max' => 'Ukuran KK pemohon tidak boleh lebih dari 500 KB.',
            'file_surat_lahir.required' => 'Surat lahir tidak boleh kosong.',
            'file_surat_lahir.file' => 'Surat lahir harus berupa file.',
            'file_surat_lahir.mimes' => 'Surat lahir harus berformat PDF.',
            'file_surat_lahir.max' => 'Ukuran Surat lahir pemohon tidak boleh lebih dari 500 KB.',
            'file_buku_nikah.required' => 'Buku nikah tidak boleh kosong.',
            'file_buku_nikah.file' => 'Buku nikah harus berupa file.',
            'file_buku_nikah.mimes' => 'Buku nikah harus berformat PDF.',
            'file_buku_nikah.max' => 'Ukuran Buku nikah pemohon tidak boleh lebih dari 500 KB.',
            'file_sptjm_kelahiran.file' => 'File SPTJM kelahiran harus berupa file.',
            'file_sptjm_kelahiran.mimes' => 'File SPTJM kelahiran harus berformat PDF.',
            'file_sptjm_kelahiran.max' => 'Ukuran SPTJM kelahiran tidak boleh lebih dari 500 KB.',
            'file_sptjm_pasutri.file' => 'File SPTJM pasutri harus berupa file.',
            'file_sptjm_pasutri.mimes' => 'File SPTJM pasutri harus berformat PDF.',
            'file_sptjm_pasutri.max' => 'Ukuran SPTJM pasutri tidak boleh lebih dari 500 KB.',
            'file_berita_acara_polisi.file' => 'File berita acara polisi harus berupa file.',
            'file_berita_acara_polisi.mimes' => 'File berita acara polisi harus berformat PDF.',
            'file_berita_acara_polisi.max' => 'Ukuran berita acara polisi tidak boleh lebih dari 500 KB.',
        ]);
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

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

        $data = $request->except([
            'formulir_f201', 'ktp_pemohon','ktp_saksi1','ktp_saksi2','kk_pemohon', 'file_surat_lahir','file_buku_nikah','file_sptjm_kelahiran','file_sptjm_pasutri','file_berita_acara_polisi','foto_wajah'
        ]);
        $data['nomor_antrian'] = $antrian->nomor_antrian;
        $data['uuid'] = Str::uuid();
        $data['status'] = 'Verifikasi Data';
        $fileFields = [
            'formulir_f201', 
            'ktp_pemohon',
            'ktp_saksi1',
            'ktp_saksi2',
            'kk_pemohon', 
            'file_surat_lahir',
            'file_buku_nikah',
            'file_sptjm_kelahiran',
            'file_sptjm_pasutri',
            'file_berita_acara_polisi'
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('akte_lahir', 'private');
            }
        }
        if ($request->filled('foto_wajah')) {
            $base64   = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_wajah);
            $decoded  = base64_decode($base64);
            $filename = 'wajah_' . uniqid() . '_' . time() . '.jpg';
            Storage::disk('private')->put("akte_lahir/{$filename}", $decoded);
            $data['foto_wajah'] = "akte_lahir/{$filename}";
        }
        AkteLahir::create($data);
        $antrian->update(['status_antrian' => 'Verifikasi Data']);

        // Kirim notifikasi ke admin
        AdminNotificationService::layananAkteLahirBaru(
            $data['nama_pemohon'],
            $data['nomor_antrian']
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data dan dokumen Akta Kelahiran berhasil dikirim.',
            ]);
        }

        return redirect()->route('layanan-mandiri')
            ->with('success', 'Data dan dokumen berhasil dikirim.');
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

    public function daftar_aktelahir(Request $request)
    {
        // Hanya tampilkan data yang antriannya sudah dimulai admin (status_antrian != 'Menunggu')
        $startedAntrianSubquery = function ($q) {
            $q->select('nomor_antrian')
              ->from('antrian_online')
              ->where('status_antrian', '!=', 'Menunggu');
        };

        $query = AkteLahir::query()->whereIn('nomor_antrian', $startedAntrianSubquery);
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $dataAkteLahir = $query->get();

        $baseCount = AkteLahir::whereIn('nomor_antrian', $startedAntrianSubquery);
        $jumlahAkteLahir    = (clone $baseCount)->count();
        $menungguVerifikasi = (clone $baseCount)->where('status','Verifikasi Data')->count();
        $dalamProses        = (clone $baseCount)->where('status','Proses Cetak')->count();
        $selesai            = (clone $baseCount)->where('status','Siap Pengambilan')->count();
        return view('admin.penerbitan_akte_lahir', compact('dataAkteLahir','jumlahAkteLahir','menungguVerifikasi','dalamProses','selesai'));
    }
    
    public function detail($uuid){
        $berkas = AkteLahir::where('uuid', $uuid)->firstOrFail();
        return view('admin.penerbitan_akte_lahir_detail', compact('berkas'));
    }

    public function updateStatus(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'alasan' => 'nullable|required_if:status,Tolak|string',
        ], [
            'status.required' => 'Status tidak boleh kosong.',
            'status.string' => 'Status harus berupa teks.',
            'alasan.required_if' => 'Alasan penolakan harus diisi jika status adalah Tolak.',
            'alasan.string' => 'Alasan harus berupa teks.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $akteLahir = AkteLahir::where('uuid', $uuid)->firstOrFail();
        $akteLahir->status = $request->status;
        if ($request->status == 'Tolak') {
            $akteLahir->alasan_penolakan = $request->alasan;
        } else {
            $akteLahir->alasan_penolakan = null;
        }
        $akteLahir->save();

        $antrianId = Antrian_Online_Model::where('nomor_antrian', $akteLahir->nomor_antrian)->value('antrian_online_id');
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

    public function lihatBerkas($uuid, $field)
    {
        $berkas = AkteLahir::where('uuid', $uuid)->firstOrFail();
        $path = $berkas->$field;
        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }
        return Storage::disk('private')->response($path);
    }

    /**
     * Admin mengunggah berkas final (hasil cetak) untuk diserahkan ke pemohon.
     * Hanya boleh dijalankan ketika status sudah "Proses Cetak".
     */
    public function uploadBerkasFinal(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'file_berkas' => 'required|file|mimes:pdf|max:2048',
        ], [
            'file_berkas.required' => 'File berkas wajib diunggah.',
            'file_berkas.file'     => 'Berkas tidak valid.',
            'file_berkas.mimes'    => 'Format yang diizinkan: PDF.',
            'file_berkas.max'      => 'Ukuran file maksimal 2 MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with('upload_error', $validator->errors()->first());
        }

        $akteLahir = AkteLahir::where('uuid', $uuid)->firstOrFail();

        $file     = $request->file('file_berkas');
        $ext      = $file->getClientOriginalExtension();
        $filename = 'akte-lahir-' . Str::slug($akteLahir->nama_pemohon ?? 'pemohon') . '-' . time() . '.' . $ext;
        $path     = $file->storeAs('berkas-final/akte-lahir', $filename, 'private');

        $antrianId = Antrian_Online_Model::where('nomor_antrian', $akteLahir->nomor_antrian)->value('antrian_online_id');
        if ($antrianId) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrianId,
                'status'            => 'Berkas Siap Diunduh',
                'tanggal'           => now()->toDateString(),
                'keterangan'        => 'Berkas Akta Kelahiran telah diunggah oleh admin. Silakan unduh.',
                'file_berkas'       => $path,
            ]);
            Antrian_Online_Model::where('antrian_online_id', $antrianId)->update(['status_antrian' => 'Selesai']);
        }

        $akteLahir->update(['status' => 'Selesai']);

        return redirect()->back()->with('success', 'Berkas berhasil diunggah dan dapat diunduh oleh pemohon.');
    }
}
