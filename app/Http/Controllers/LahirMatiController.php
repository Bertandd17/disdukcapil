<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LahirMati;
use App\Models\Antrian_Online_Model;
use App\Models\Lacak_Berkas_Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LahirMatiController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id'                  => 'required|string|exists:layanan,layanan_id',
            'nomor_antrian'               => 'required|string',
            'nik_pemohon'                 => 'required|string|digits:16',
            'nomor_kk_pemohon'            => 'nullable|string|digits:16',
            'nama_pemohon'                => 'required|string',
            'alamat_pemohon'              => 'required|string',
            'hubungan_pemohon'            => 'required|string',
            'foto_wajah'                  => 'nullable|string',
            'ktp_pemohon'                 => 'nullable|file|mimes:pdf|max:2048',
            'kartu_keluarga_pemohon'      => 'nullable|file|mimes:pdf|max:2048',
            'ktp_saksi1'                  => 'nullable|file|mimes:pdf|max:2048',
            'ktp_saksi2'                  => 'nullable|file|mimes:pdf|max:2048',
            'formulir_f201'               => 'nullable|file|mimes:pdf|max:2048',
            'surat_keterangan_lahir_mati' => 'nullable|file|mimes:pdf|max:2048',
        ], [
            'digits' => 'Pastikan nomor NIK/KK tepat 16 angka!',
            'mimes'  => 'Berkas yang diunggah harus berformat PDF!',
            'max'    => 'Ukuran berkas maksimal adalah 2MB.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()
                ->with('error', [
                    'title'   => 'Validasi Gagal.',
                    'message' => $validator->errors()->first(),
                ])
                ->withInput();
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

        try {
            $nomorAntrian = $antrian->nomor_antrian;

            // Ambil data teks, kecualikan field file
            $data = $request->except([
                'ktp_pemohon', 'kartu_keluarga_pemohon', 'ktp_saksi1',
                'ktp_saksi2', 'formulir_f201', 'surat_keterangan_lahir_mati',
                'foto_wajah',
            ]);

            $data['status']     = 'Verifikasi Data';
            $data['layanan_id'] = 'lahir_mati';

            // Timpa nomor antrian dengan nilai resmi dari DB
            $data['nomor_antrian'] = $nomorAntrian;

            // Handle file uploads ke disk 'private'
            $fileUploads = [
                'ktp_pemohon'                 => 'lahir_mati/pemohon',
                'kartu_keluarga_pemohon'      => 'lahir_mati/kk',
                'ktp_saksi1'                  => 'lahir_mati/saksi',
                'ktp_saksi2'                  => 'lahir_mati/saksi',
                'formulir_f201'               => 'lahir_mati/formulir',
                'surat_keterangan_lahir_mati' => 'lahir_mati/surat',
            ];

            foreach ($fileUploads as $inputName => $storagePath) {
                if ($request->hasFile($inputName)) {
                    $data[$inputName] = $request->file($inputName)->store($storagePath, 'private');
                }
            }

            if ($request->filled('foto_wajah')) {
                $base64   = preg_replace('/^data:image\/\w+;base64,/', '', $request->foto_wajah);
                $decoded  = base64_decode($base64);
                $filename = 'wajah_' . uniqid() . '_' . time() . '.jpg';
                Storage::disk('private')->put("lahir_mati/{$filename}", $decoded);
                $data['foto_wajah'] = "lahir_mati/{$filename}";
            }

            // Simpan ke tabel lahir_mati
            $lahirMati = LahirMati::create($data);

            // Update antrian_online_id pada record lahir mati
            $lahirMati->update(['antrian_online_id' => $antrian->antrian_online_id]);

            // Update status antrian
            $antrian->update(['status_antrian' => 'Verifikasi Data']);

            // Buat lacak berkas
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrian->antrian_online_id,
                'status'            => 'Verifikasi Data',
                'tanggal'           => now()->toDateString(),
                'keterangan'        => 'Permohonan Pencatatan Lahir Mati diterima dan sedang dalam verifikasi data.',
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'       => true,
                    'message'       => 'Permohonan Lahir Mati berhasil dikirim! Nomor Antrian Anda: ' . $nomorAntrian,
                    'nomor_antrian' => $nomorAntrian,
                ]);
            }

            return redirect()->back()->with('success', 'Permohonan Lahir Mati berhasil dikirim! Nomor Antrian Anda: ' . $nomorAntrian);

        } catch (\Exception $e) {
            $safeErrorMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $safeErrorMessage,
                ], 500);
            }
            return back()
                ->with('error', [
                    'title'   => 'Gagal menyimpan data',
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }
    }

    private function invalidAntrianResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with('error', [
            'title'   => 'Nomor Antrian Tidak Valid.',
            'message' => $message,
        ])->withInput();
    }

    public function daftar(Request $request)
    {
        $startedAntrianSubquery = function ($q) {
            $q->select('nomor_antrian')
              ->from('antrian_online')
              ->where('status_antrian', '!=', 'Menunggu');
        };

        $query = LahirMati::query()
            ->whereIn('layanan_id', ['lahir_mati'])
            ->whereIn('nomor_antrian', $startedAntrianSubquery);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $dataLahirMati = $query->latest()->get();

        $baseCount          = LahirMati::whereIn('layanan_id', ['lahir_mati'])->whereIn('nomor_antrian', $startedAntrianSubquery);
        $jumlah             = (clone $baseCount)->count();
        $menungguVerifikasi = (clone $baseCount)->where('status', 'Verifikasi Data')->count();
        $dalamProses        = (clone $baseCount)->where('status', 'Proses Cetak')->count();
        $selesai            = (clone $baseCount)->where('status', 'Siap Pengambilan')->count();

        return view('admin.penerbitan_lahir_mati', compact('dataLahirMati', 'jumlah', 'menungguVerifikasi', 'dalamProses', 'selesai'));
    }

    public function detail($uuid)
    {
        $berkas = LahirMati::where('uuid', $uuid)->firstOrFail();
        return view('admin.penerbitan_lahir_mati_detail', compact('berkas'));
    }

    public function updateStatus(Request $request, $uuid)
    {
        $lahirMati = LahirMati::where('uuid', $uuid)->firstOrFail();
        $lahirMati->status = $request->status;
        $alasan = $request->input('alasan_penolakan') ?? $request->input('alasan');
        if ($request->status == 'Tolak') {
            $lahirMati->alasan_penolakan = $alasan;
        }
        $lahirMati->save();

        $antrianId = $lahirMati->antrian_online_id
            ?? Antrian_Online_Model::where('nomor_antrian', $lahirMati->nomor_antrian)->value('antrian_online_id');

        if ($antrianId) {
            $statusAntrian = $request->status === 'Tolak' ? 'Ditolak' : $request->status;
            Antrian_Online_Model::where('antrian_online_id', $antrianId)->update(['status_antrian' => $statusAntrian]);
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrianId,
                'status'            => $request->status,
                'tanggal'           => now()->toDateString(),
                'keterangan'        => $request->status === 'Tolak'
                    ? 'Permohonan ditolak. Alasan: ' . ($alasan ?? '-')
                    : 'Status diperbarui menjadi ' . $request->status . '.',
            ]);
        }

        return redirect()->back()->with('success', 'Status berhasil diperbarui');
    }

    public function uploadBerkasFinal(Request $request, $uuid)
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

        $lahirMati = LahirMati::where('uuid', $uuid)->firstOrFail();

        $file     = $request->file('file_berkas');
        $ext      = $file->getClientOriginalExtension();
        $filename = 'lahir-mati-' . Str::slug($lahirMati->nama_pemohon ?? 'pemohon') . '-' . time() . '.' . $ext;
        $path     = $file->storeAs('berkas-final/lahir-mati', $filename, 'private');

        $antrianId = $lahirMati->antrian_online_id
            ?? Antrian_Online_Model::where('nomor_antrian', $lahirMati->nomor_antrian)->value('antrian_online_id');

        if ($antrianId) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrianId,
                'status'            => 'Berkas Siap Diunduh',
                'tanggal'           => now()->toDateString(),
                'keterangan'        => 'Surat Keterangan Lahir Mati telah diunggah oleh admin. Silakan unduh.',
                'file_berkas'       => $path,
            ]);
            Antrian_Online_Model::where('antrian_online_id', $antrianId)->update(['status_antrian' => 'Selesai']);
        }

        $lahirMati->update(['status' => 'Selesai']);

        return redirect()->back()->with('success', 'Berkas berhasil diunggah dan dapat diunduh oleh pemohon.');
    }

    /**
     * Lihat berkas dari private disk
     */
    public function lihatBerkas($uuid, $field)
    {
        $berkas = LahirMati::where('uuid', $uuid)->firstOrFail();
        $path   = $berkas->$field;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->response($path);
    }
}