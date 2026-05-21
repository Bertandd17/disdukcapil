<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AkteKematian;
use App\Models\Antrian_Online_Model;
use App\Models\Lacak_Berkas_Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AkteKematianController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id'                => 'required|string|exists:layanan,layanan_id',
            'nomor_antrian'             => 'required|string',
            'nik_pemohon'               => 'required|string|digits:16',
            'nomor_kk_pemohon'          => 'nullable|string|digits:16',
            'nama_pemohon'              => 'required|string',
            'alamat_pemohon'            => 'required|string',
            'hubungan_pemohon'          => 'required|string',
            
            'ktp_pemohon'               => 'nullable|file|mimes:pdf|max:5120',
            'kartu_keluarga_pemohon'    => 'nullable|file|mimes:pdf|max:5120',
            'formulir_f201'             => 'nullable|file|mimes:pdf|max:5120',
            'surat_keterangan_kematian' => 'nullable|file|mimes:pdf|max:5120',
            'ktp_almarhum'              => 'nullable|file|mimes:pdf|max:5120',
            'ktp_saksi1'                => 'nullable|file|mimes:pdf|max:5120',
            'ktp_saksi2'                => 'nullable|file|mimes:pdf|max:5120',
        ], [
            'digits' => 'Pastikan nomor NIK/KK tepat 16 angka!',
            'mimes'  => 'Berkas yang diunggah harus berformat PDF!',
            'max'    => 'Ukuran berkas maksimal adalah 5MB.'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'Validasi Gagal:<br>' . implode('<br>', $validator->errors()->all()))
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

            // 2. Ambil semua data teks
            $data = $request->except([
                'ktp_pemohon', 'kartu_keluarga_pemohon', 'formulir_f201', 
                'surat_keterangan_kematian', 'ktp_almarhum', 'ktp_saksi1', 'ktp_saksi2'
            ]);
            
            $data['status'] = 'Verifikasi Data';
            
            // 3. INI KUNCINYA: Timpa input asal-asalan pemohon dengan Token Resmi
            $data['nomor_antrian'] = $nomorAntrian; 

            // 4. Handle file uploads
            $fileUploads = [
                'ktp_pemohon'               => 'akte_kematian/pemohon',
                'kartu_keluarga_pemohon'    => 'akte_kematian/kk',
                'formulir_f201'             => 'akte_kematian/formulir',
                'surat_keterangan_kematian' => 'akte_kematian/surat',
                'ktp_almarhum'              => 'akte_kematian/almarhum',
                'ktp_saksi1'                => 'akte_kematian/saksi',
                'ktp_saksi2'                => 'akte_kematian/saksi',
            ];

            foreach ($fileUploads as $inputName => $storagePath) {
                if ($request->hasFile($inputName)) {
                    $data[$inputName] = $request->file($inputName)->store($storagePath, 'public');
                }
            }

            // 5. Simpan ke database Akte Kematian
            $akteKematian = AkteKematian::create($data);

            // 6. Tandai nomor antrian yang sudah ada sebagai mulai diproses
            $antrian->update(['status_antrian' => 'Verifikasi Data']);

            // 7. Update relasi
            $akteKematian->update(['antrian_online_id' => $antrian->antrian_online_id]);

            // 8. Create lacak berkas
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrian->antrian_online_id,
                'status'            => 'Verifikasi Data',
                'tanggal'           => now()->toDateString(),
                'keterangan'        => 'Permohonan Akte Kematian diterima dan sedang dalam verifikasi data.',
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'       => true,
                    'message'       => 'Permohonan Akte Kematian berhasil dikirim! Nomor Antrian Anda: ' . $nomorAntrian,
                    'nomor_antrian' => $nomorAntrian,
                ]);
            }
            return redirect()->back()->with('success', 'Permohonan Akte Kematian berhasil dikirim! Nomor Antrian Anda: ' . $nomorAntrian);

        } catch (\Exception $e) {
            $safeErrorMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $safeErrorMessage,
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $safeErrorMessage)->withInput();
        }
    }

    private function generateNomorAntrian()
    {
        // Format wajib Antrian Online: 3 Huruf - 3 Angka - 3 Angka (Contoh: AKT-106-001)
        $huruf = 'AKT'; // 3 Huruf penanda Akte Kematian
        
        // 3 Angka bagian tengah: Mengambil urutan hari dalam setahun (001 - 365)
        $hariKe = str_pad(date('z') + 1, 3, '0', STR_PAD_LEFT); 
        
        // 3 Angka bagian akhir: Urutan pendaftar hari ini
        $count = AkteKematian::whereDate('created_at', now())->count() + 1;
        $urutan = str_pad($count, 3, '0', STR_PAD_LEFT);
        
        return "{$huruf}-{$hariKe}-{$urutan}";
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

    // ... method daftar, detail, dan updateStatus tetap sama ...
    public function daftar(Request $request)
    {
        // Hanya tampilkan data yang antriannya sudah dimulai admin (status_antrian != 'Menunggu')
        $startedAntrianSubquery = function ($q) {
            $q->select('nomor_antrian')
              ->from('antrian_online')
              ->where('status_antrian', '!=', 'Menunggu');
        };

        $query = AkteKematian::query()->whereIn('nomor_antrian', $startedAntrianSubquery);
        if ($request->status) $query->where('status', $request->status);
        $dataKematian = $query->latest()->get();

        $baseCount = AkteKematian::whereIn('nomor_antrian', $startedAntrianSubquery);
        $jumlah             = (clone $baseCount)->count();
        $menungguVerifikasi = (clone $baseCount)->where('status', 'Verifikasi Data')->count();
        $dalamProses        = (clone $baseCount)->where('status', 'Proses Cetak')->count();
        $selesai            = (clone $baseCount)->where('status', 'Siap Pengambilan')->count();

        return view('admin.penerbitan_akte_kematian', compact('dataKematian', 'jumlah', 'menungguVerifikasi', 'dalamProses', 'selesai'));
    }

    public function detail($uuid)
    {
        $berkas = AkteKematian::where('uuid', $uuid)->firstOrFail();
        return view('admin.penerbitan_akte_kematian_detail', compact('berkas'));
    }

    public function updateStatus(Request $request, $uuid)
    {
        $kematian = AkteKematian::where('uuid', $uuid)->firstOrFail();
        $kematian->status = $request->status;
        $alasan = $request->input('alasan_penolakan') ?? $request->input('alasan');
        if ($request->status == 'Tolak') $kematian->alasan_penolakan = $alasan;
        $kematian->save();

        $antrianId = $kematian->antrian_online_id ?? Antrian_Online_Model::where('nomor_antrian', $kematian->nomor_antrian)->value('antrian_online_id');
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
            'file_berkas' => 'required|file|mimes:pdf|max:5120',
        ], [
            'file_berkas.required' => 'File berkas wajib diunggah.',
            'file_berkas.mimes'    => 'Format yang diizinkan: PDF.',
            'file_berkas.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with('upload_error', $validator->errors()->first());
        }

        $kematian = AkteKematian::where('uuid', $uuid)->firstOrFail();

        $file     = $request->file('file_berkas');
        $ext      = $file->getClientOriginalExtension();
        $filename = 'akte-kematian-' . Str::slug($kematian->nama_pemohon ?? 'pemohon') . '-' . time() . '.' . $ext;
        $path     = $file->storeAs('berkas-final/akte-kematian', $filename, 'private');

        $antrianId = $kematian->antrian_online_id ?? Antrian_Online_Model::where('nomor_antrian', $kematian->nomor_antrian)->value('antrian_online_id');
        if ($antrianId) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrianId,
                'status'            => 'Berkas Siap Diunduh',
                'tanggal'           => now()->toDateString(),
                'keterangan'        => 'Berkas Akta Kematian telah diunggah oleh admin. Silakan unduh.',
                'file_berkas'       => $path,
            ]);
            Antrian_Online_Model::where('antrian_online_id', $antrianId)->update(['status_antrian' => 'Selesai']);
        }

        $kematian->update(['status' => 'Selesai']);

        return redirect()->back()->with('success', 'Berkas berhasil diunggah dan dapat diunduh oleh pemohon.');
    }
}
