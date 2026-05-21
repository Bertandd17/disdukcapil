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
            
            'ktp_pemohon'                 => 'nullable|file|mimes:pdf|max:5120',
            'kartu_keluarga_pemohon'      => 'nullable|file|mimes:pdf|max:5120',
            'ktp_saksi1'                  => 'nullable|file|mimes:pdf|max:5120',
            'ktp_saksi2'                  => 'nullable|file|mimes:pdf|max:5120',
            'formulir_f201'               => 'nullable|file|mimes:pdf|max:5120',
            'surat_keterangan_lahir_mati' => 'nullable|file|mimes:pdf|max:5120',
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

            // 2. Ambil data teks
            $data = $request->except([
                'ktp_pemohon', 'kartu_keluarga_pemohon', 'ktp_saksi1', 
                'ktp_saksi2', 'formulir_f201', 'surat_keterangan_lahir_mati'
            ]);
            
            $data['status'] = 'Verifikasi Data';
            
            // 3. INI KUNCINYA: Timpa input asal-asalan pemohon dengan Token Resmi
            $data['nomor_antrian'] = $nomorAntrian;

            // 4. Handle file uploads 
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
                    $data[$inputName] = $request->file($inputName)->store($storagePath, 'public');
                }
            }

            // 5. Simpan ke Database
            $lahirMati = LahirMati::create($data);

            // 6. Tandai nomor antrian yang sudah ada sebagai mulai diproses
            $antrian->update(['status_antrian' => 'Verifikasi Data']);

            // 7. Update record Lahir Mati
            $lahirMati->update(['antrian_online_id' => $antrian->antrian_online_id]);

            // 8. Create lacak berkas record
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
                    'message' => 'Gagal menyimpan data: ' . $safeErrorMessage,
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $safeErrorMessage)->withInput();
        }
    }

    private function generateNomorAntrian()
    {
        // Format wajib Antrian Online: 3 Huruf - 3 Angka - 3 Angka (Contoh: LMT-106-001)
        $huruf = 'LMT'; // 3 Huruf penanda Lahir Mati
        
        // 3 Angka bagian tengah: Mengambil urutan hari dalam setahun (001 - 365)
        $hariKe = str_pad(date('z') + 1, 3, '0', STR_PAD_LEFT); 
        
        // 3 Angka bagian akhir: Urutan pendaftar hari ini
        $count = LahirMati::whereDate('created_at', now())->count() + 1;
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

        $query = LahirMati::query()->whereIn('nomor_antrian', $startedAntrianSubquery);
        if ($request->status) $query->where('status', $request->status);
        $dataLahirMati = $query->latest()->get();

        $baseCount = LahirMati::whereIn('nomor_antrian', $startedAntrianSubquery);
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
        if ($request->status == 'Tolak') $lahirMati->alasan_penolakan = $alasan;
        $lahirMati->save();

        $antrianId = $lahirMati->antrian_online_id ?? Antrian_Online_Model::where('nomor_antrian', $lahirMati->nomor_antrian)->value('antrian_online_id');
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

        $lahirMati = LahirMati::where('uuid', $uuid)->firstOrFail();

        $file     = $request->file('file_berkas');
        $ext      = $file->getClientOriginalExtension();
        $filename = 'lahir-mati-' . Str::slug($lahirMati->nama_pemohon ?? 'pemohon') . '-' . time() . '.' . $ext;
        $path     = $file->storeAs('berkas-final/lahir-mati', $filename, 'private');

        $antrianId = $lahirMati->antrian_online_id ?? Antrian_Online_Model::where('nomor_antrian', $lahirMati->nomor_antrian)->value('antrian_online_id');
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
}
