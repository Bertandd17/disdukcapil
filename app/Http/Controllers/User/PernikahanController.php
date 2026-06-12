<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPernikahanRequest;
use App\Models\Antrian_Online_Model;
use App\Models\DokumenPernikahan;
use App\Models\LayananPernikahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PernikahanController extends Controller
{
    /**
     * Halaman form pernikahan
     * GET /layanan-mandiri/pernikahan
     */
    public function index()
    {
        $userPernikahan = [];
        if (auth()->check()) {
            $userPernikahan = LayananPernikahan::where('user_id', auth()->id())
                ->with(['dokumen'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('pages.pernikahan', compact('userPernikahan'));
    }

    /**
     * Halaman form pembuatan permohonan baru
     * GET /layanan-mandiri/pernikahan/create
     */
    public function create()
    {
        $listKeagamaan = DB::table('organisasi')
            ->select(['organisasi_id', 'nama_organisasi', 'jenis_organisasi', 'alamat'])
            ->where('status', 'aktif')
            ->get();

        return view('pages.pernikahan-create', compact('listKeagamaan'));
    }

    /**
     * Submit permohonan pernikahan
     * POST /layanan-mandiri/pernikahan
     */
    public function store(SubmitPernikahanRequest $request)
    {
        try {
            DB::beginTransaction();

            $pernikahan = LayananPernikahan::create([
                'user_id' => auth()->id(),
                'nama_pemohon' => $request->nama_pemohon,
                'nik_pemohon' => $request->nik_pemohon,
                'alamat_pemohon' => $request->alamat_pemohon,
                'nama_mempelai_pria' => $request->nama_mempelai_pria,
                'nik_mempelai_pria' => $request->nik_mempelai_pria,
                'tempat_lahir_mempelai_pria' => $request->tempat_lahir_mempelai_pria,
                'tanggal_lahir_mempelai_pria' => $request->tanggal_lahir_mempelai_pria,
                'agama_mempelai_pria' => $request->agama_mempelai_pria,
                'alamat_mempelai_pria' => $request->alamat_mempelai_pria,
                'pekerjaan_mempelai_pria' => $request->pekerjaan_mempelai_pria,
                'nama_mempelai_wanita' => $request->nama_mempelai_wanita,
                'nik_mempelai_wanita' => $request->nik_mempelai_wanita,
                'tempat_lahir_mempelai_wanita' => $request->tempat_lahir_mempelai_wanita,
                'tanggal_lahir_mempelai_wanita' => $request->tanggal_lahir_mempelai_wanita,
                'agama_mempelai_wanita' => $request->agama_mempelai_wanita,
                'alamat_mempelai_wanita' => $request->alamat_mempelai_wanita,
                'pekerjaan_mempelai_wanita' => $request->pekerjaan_mempelai_wanita,
                'nama_ayah_pria' => $request->nama_ayah_pria,
                'nik_ayah_pria' => $request->nik_ayah_pria,
                'tempat_lahir_ayah_pria' => $request->tempat_lahir_ayah_pria,
                'tanggal_lahir_ayah_pria' => $request->tanggal_lahir_ayah_pria,
                'alamat_ayah_pria' => $request->alamat_ayah_pria,
                'nama_ibu_pria' => $request->nama_ibu_pria,
                'nik_ibu_pria' => $request->nik_ibu_pria,
                'tempat_lahir_ibu_pria' => $request->tempat_lahir_ibu_pria,
                'tanggal_lahir_ibu_pria' => $request->tanggal_lahir_ibu_pria,
                'alamat_ibu_pria' => $request->alamat_ibu_pria,
                'nama_saksi_1' => $request->nama_saksi_1,
                'nik_saksi_1' => $request->nik_saksi_1,
                'tempat_lahir_saksi_1' => $request->tempat_lahir_saksi_1,
                'tanggal_lahir_saksi_1' => $request->tanggal_lahir_saksi_1,
                'alamat_saksi_1' => $request->alamat_saksi_1,
                'nama_saksi_2' => $request->nama_saksi_2,
                'nik_saksi_2' => $request->nik_saksi_2,
                'tempat_lahir_saksi_2' => $request->tempat_lahir_saksi_2,
                'tanggal_lahir_saksi_2' => $request->tanggal_lahir_saksi_2,
                'alamat_saksi_2' => $request->alamat_saksi_2,
                'keagamaan_id' => $request->keagamaan_id,
                'nama_gereja' => $request->nama_gereja,
                'tanggal_perkawinan' => $request->tanggal_perkawinan,
                'status' => LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
            ]);

            DB::commit();

            return redirect()
                ->route('pernikahan.show', $pernikahan->pernikahan_id)
                ->with('success', 'Permohonan pernikahan berhasil diajukan. Nomor antrian: ' . $pernikahan->nomor_antrian);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal submit pernikahan: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal mengajukan permohonan. Silakan coba lagi.');
        }
    }

    /**
     * Detail permohonan pernikahan
     * GET /layanan-mandiri/pernikahan/{id}
     */
    public function show(string $id)
    {
        $pernikahan = LayananPernikahan::with(['dokumen', 'history'])
            ->where('pernikahan_id', $id)
            ->firstOrFail();

        if (auth()->id() !== $pernikahan->user_id && !auth()->user()?->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $jenisDokumen = DokumenPernikahan::JENIS_DOKUMEN;

        return view('pages.pernikahan-detail', compact('pernikahan', 'jenisDokumen'));
    }

    /**
     * Upload dokumen pernikahan
     * POST /layanan-mandiri/pernikahan/{id}/upload
     */
    public function uploadDokumen(Request $request, string $id)
    {
        $request->validate([
            'jenis_dokumen' => 'required|string|in:surat_keterangan,ktp_mempelai_pria,ktp_mempelai_wanita,kk_mempelai_pria,kk_mempelai_wanita,surat_ijin_orang_tua,surat_n1_n2_n4,foto_prewedding,bukti_pembayaran,lainnya',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->where(function ($q) {
                    $q->where('user_id', auth()->id())
                        ->orWhereHas('user', function ($query) {
                            $query->whereHas('roles', function ($q) {
                                $q->where('name', 'admin');
                            });
                        });
                })
                ->firstOrFail();

            if (!$pernikahan->canUploadDocuments()) {
                return back()->with('error', 'Tidak dapat mengupload dokumen. Status atau deadline tidak memenuhi syarat.');
            }

            DB::beginTransaction();

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $path = $file->storeAs('pernikahan/' . $id, $filename, 'public');

            DokumenPernikahan::create([
                'pernikahan_id' => $id,
                'jenis_dokumen' => $request->jenis_dokumen,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => DokumenPernikahan::STATUS_UPLOADED,
            ]);

            $hasAllRequiredDocs = $pernikahan->dokumen()
                ->whereIn('jenis_dokumen', ['surat_keterangan', 'ktp_mempelai_pria', 'ktp_mempelai_wanita'])
                ->where('status', '!=', DokumenPernikahan::STATUS_DITOLAK)
                ->count() >= 3;

            if ($hasAllRequiredDocs) {
                $pernikahan->update([
                    'status' => LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Dokumen berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal upload dokumen: ' . $e->getMessage());

            return back()->with('error', 'Gagal mengupload dokumen. Silakan coba lagi.');
        }
    }

    /**
     * Hapus dokumen yang sudah diupload
     * DELETE /layanan-mandiri/pernikahan/{pernikahan_id}/dokumen/{dokumen_id}
     */
    public function deleteDokumen(string $pernikahanId, int $dokumenId)
    {
        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $pernikahanId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $dokumen = DokumenPernikahan::where('id', $dokumenId)
                ->where('pernikahan_id', $pernikahanId)
                ->firstOrFail();

            if (!in_array($pernikahan->status, [
                LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
            ])) {
                return back()->with('error', 'Dokumen tidak dapat dihapus pada status ini.');
            }

            Storage::disk('public')->delete($dokumen->file_path);
            $dokumen->delete();

            return back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal hapus dokumen: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghapus dokumen.');
        }
    }

    /**
     * Submit permohonan pernikahan dari layanan mandiri (tanpa login)
     * POST /layanan-mandiri/perkawinan
     */
    public function storeFromLayananMandiri(Request $request)
    {
        // Debug logging - TEPAT di awal method
        Log::info('=== storeFromLayananMandiri CALLED ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'has_files' => $request->hasFile('ktp_mempelai_pria'),
            'all_input_keys' => array_keys($request->all()),
        ]);

        Log::info('=== FORM DATA ===', [
            'nomor_antrian' => $request->input('nomor_antrian'),
            'nama_pemohon' => $request->input('nama_pemohon'),
            'nik_pemohon' => $request->input('nik_pemohon'),
            'alamat_pemohon' => $request->input('alamat_pemohon'),
            'jenis_agama' => $request->input('jenis_agama'),
            'keagamaan_id' => $request->input('keagamaan_id'),
            'tanggal_perkawinan' => $request->input('tanggal_perkawinan'),
        ]);

        $nomorTrimmed = trim($request->input('nomor_antrian', ''));
        $layananId = $request->input('layanan_id');

        if ($nomorTrimmed && $layananId) {
            $antrian = Antrian_Online_Model::with('layanan')
                ->cariNomorExact($nomorTrimmed)
                ->first();

            if (!$antrian) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'NOT_FOUND',
                    'message' => 'Nomor antrian tidak ditemukan dalam sistem.',
                ], 422);
            }

            $validasiLayanan = $antrian->validateForLayanan($layananId);
            if (!$validasiLayanan['valid']) {
                return response()->json([
                    'success' => false,
                    'error_code' => $validasiLayanan['error_code'] ?? 'VALIDATION_ERROR',
                    'message' => strip_tags($validasiLayanan['message']),
                ], 422);
            }
        }

        // Validasi manual untuk mengontrol response JSON
        $validator = \Validator::make($request->all(), [
            'layanan_id' => 'required|string|exists:layanan,layanan_id',
            'nomor_antrian' => 'required|string',
            'nama_pemohon' => 'required|string',
            'nik_pemohon' => 'required|string|size:16',
            'alamat_pemohon' => 'required|string',
            'jenis_agama' => 'required|integer',
            'keagamaan_id' => 'required|string',
            'tanggal_perkawinan' => 'required|date|after:today',
            'ktp_mempelai_pria' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ktp_mempelai_wanita' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ktp_saksi_1' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ktp_saksi_2' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'tanggal_perkawinan.after' => 'Tanggal perkawinan minimal harus 7 hari dari hari ini.',
            'nik_pemohon.size' => 'NIK pemohon harus 16 digit.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Ambil data keagamaan untuk nama gereja
            $keagamaan = DB::table('keagamaan')
                ->join('users', 'keagamaan.user_id', '=', 'users.id')
                ->where('keagamaan.keagamaan_id', $request->keagamaan_id)
                ->first();

            $namaGereja = $keagamaan ? $keagamaan->name : $request->keagamaan_id;

            // Untuk layanan mandiri, field mempelai diisi dengan data pemohon sebagai placeholder sementara
            // Data lengkap akan diisi setelah konfirmasi keagamaan
            $pernikahan = LayananPernikahan::create([
                'pernikahan_id' => (string) Str::uuid(),
                'nomor_antrian' => $request->nomor_antrian,
                'user_id' => auth()->check() ? auth()->id() : null,
                'nama_pemohon' => $request->nama_pemohon,
                'nik_pemohon' => $request->nik_pemohon,
                'alamat_pemohon' => $request->alamat_pemohon,
                // Data mempelai pria (placeholder dari pemohon)
                'nama_mempelai_pria' => $request->nama_pemohon,
                'nik_mempelai_pria' => $request->nik_pemohon,
                'alamat_mempelai_pria' => $request->alamat_pemohon,
                // Data mempelai wanita (placeholder sementara - akan diupdate keagamaan)
                'nama_mempelai_wanita' => '',
                'nik_mempelai_wanita' => '0000000000000001',
                'alamat_mempelai_wanita' => '-',
                // Data saksi (placeholder sementara)
                'nama_saksi_1' => '',
                'nik_saksi_1' => '0000000000000002',
                'alamat_saksi_1' => '-',
                'nama_saksi_2' => '',
                'nik_saksi_2' => '0000000000000003',
                'alamat_saksi_2' => '-',
                // Data keagamaan
                'keagamaan_id' => $request->keagamaan_id,
                'nama_gereja' => $namaGereja,
                'tanggal_perkawinan' => $request->tanggal_perkawinan,
                'status' => LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
            ]);

            // Handle file uploads - simpan path ke tabel layanan_pernikahan
            $fileMapping = [
                'ktp_mempelai_pria' => 'file_ktp_mempelai_pria',
                'ktp_mempelai_wanita' => 'file_ktp_mempelai_wanita',
                'ktp_saksi_1' => 'file_ktp_saksi_1',
                'ktp_saksi_2' => 'file_ktp_saksi_2',
            ];

            foreach ($fileMapping as $inputName => $dbColumn) {
                if ($request->hasFile($inputName)) {
                    $file = $request->file($inputName);
                    $extension = $file->getClientOriginalExtension();
                    $filename = Str::uuid() . '.' . $extension;
                    $path = $file->storeAs('pernikahan/' . $pernikahan->pernikahan_id, $filename, 'public');

                    // Update path file langsung ke tabel layanan_pernikahan
                    $pernikahan->$dbColumn = $path;
                    $pernikahan->save();
                }
            }

            // Tandai nomor antrian sebagai digunakan
            if ($nomorTrimmed && $layananId) {
                $antrian = Antrian_Online_Model::with('layanan')
                    ->cariNomorExact($nomorTrimmed)
                    ->first();

                if ($antrian) {
                    $antrian->update(['status_antrian' => 'Digunakan']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permohonan pernikahan berhasil diajukan! Silakan cek status secara berkala.',
                'data' => [
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'track_url' => route('antrian-online.detail', $pernikahan->nomor_antrian),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal submit pernikahan dari layanan mandiri: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan permohonan. Silakan coba lagi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate nomor antrian unik
     */
    private function generateNomorAntrian(): string
    {
        $prefix = 'PNK';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));

        // Cek keunikan
        $nomor = "{$prefix}-{$date}-{$random}";
        $maxAttempts = 10;
        $attempts = 0;

        while (LayananPernikahan::where('nomor_antrian', $nomor)->exists()) {
            $random = strtoupper(Str::random(4));
            $nomor = "{$prefix}-{$date}-{$random}";
            $attempts++;

            if ($attempts >= $maxAttempts) {
                // Fallback dengan timestamp
                $nomor = "{$prefix}-{$date}-" . substr((string) time(), -4);
                break;
            }
        }

        return $nomor;
    }

    /**
     * Cek status pernikahan berdasarkan nomor antrian
     * GET /api/pernikahan/status/{nomor_antrian}
     */
    public function getStatusByNomorAntrian(string $nomorAntrian)
    {
        try {
            $pernikahan = LayananPernikahan::where('nomor_antrian', $nomorAntrian)
                ->with(['dokumen' => function ($query) {
                    $query->select(['id', 'pernikahan_id', 'jenis_dokumen', 'status', 'original_filename']);
                }])
                ->first();

            if (!$pernikahan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor antrian tidak ditemukan',
                ], 404);
            }

            // Tentukan step berdasarkan status
            $stepMap = [
                LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN => 1,
                LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN => 1,
                LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL => 2,
                LayananPernikahan::STATUS_TANGGAL_DITOLAK => 2,
                LayananPernikahan::STATUS_TANGGAL_DISETUJUI => 3,
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI => 3,
                LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN => 3,
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI => 4,
                LayananPernikahan::STATUS_SELESAI => 5,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'step' => $stepMap[$pernikahan->status] ?? 1,
                    'tanggal_perkawinan' => $pernikahan->tanggal_perkawinan?->format('d F Y'),
                    'nama_gereja' => $pernikahan->nama_gereja,
                    'catatan_keagamaan' => $pernikahan->catatan_keagamaan,
                    'catatan_admin' => $pernikahan->catatan_admin,
                    'alasan_ditolak' => $pernikahan->alasan_ditolak,
                    'dokumen' => $pernikahan->dokumen->map(function ($doc) {
                        return [
                            'jenis_dokumen' => $doc->jenis_dokumen,
                            'status' => $doc->status,
                            'original_filename' => $doc->original_filename,
                        ];
                    }),
                    'can_upload_document' => $pernikahan->canUploadDocuments(),
                    'created_at' => $pernikahan->created_at->format('d M Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal cek status pernikahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status',
            ], 500);
        }
    }
}
