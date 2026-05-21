<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPernikahanRequest;
use App\Models\DokumenPernikahan;
use App\Models\LayananPernikahan;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PernikahanController extends Controller
{
    /**
     * Ambil nomor antrian untuk layanan pernikahan
     * POST /api/pernikahan/antrian
     */
    public function ambilNomorAntrian(): JsonResponse
    {
        try {
            $nomorAntrian = LayananPernikahan::generateNomorAntrian();

            return response()->json([
                'success' => true,
                'message' => 'Nomor antrian berhasil dibuat',
                'data' => [
                    'nomor_antrian' => $nomorAntrian,
                    'expired_at' => now()->addHours(24)->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal membuat nomor antrian pernikahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat nomor antrian',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan',
            ], 500);
        }
    }

    /**
     * Submit form pernikahan
     * POST /api/pernikahan/submit
     */
    public function submit(SubmitPernikahanRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $pernikahan = LayananPernikahan::create([
                'nomor_antrian' => LayananPernikahan::generateNomorAntrian(),
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

            AdminNotificationService::pernikahanBaru(
                $request->nama_pemohon,
                $pernikahan->nomor_antrian,
                $request->nama_gereja
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Layanan pernikahan berhasil diajukan',
                'data' => [
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'step' => $pernikahan->step,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal submit pernikahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan layanan pernikahan',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan',
            ], 500);
        }
    }

    /**
     * Cek status pernikahan
     * GET /api/pernikahan/status/{pernikahan_id}
     */
    public function status(string $pernikahanId): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::with(['dokumen', 'history'])
                ->where('pernikahan_id', $pernikahanId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'status_color' => $pernikahan->status_color,
                    'step' => $pernikahan->step,
                    'tanggal_perkawinan' => $pernikahan->tanggal_perkawinan?->format('d-m-Y'),
                    'can_upload_documents' => $pernikahan->canUploadDocuments(),
                    'deadline_upload' => $pernikahan->getDeadlineUpload()?->format('d-m-Y H:i'),
                    'is_deadline_passed' => $pernikahan->isDeadlinePassed(),
                    'catatan_keagamaan' => $pernikahan->catatan_keagamaan,
                    'catatan_admin' => $pernikahan->catatan_admin,
                    'alasan_ditolak' => $pernikahan->alasan_ditolak,
                    'dokumen' => $pernikahan->dokumen->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'jenis_dokumen' => $doc->jenis_dokumen,
                            'jenis_dokumen_label' => $doc->jenis_dokumen_label,
                            'status' => $doc->status,
                            'status_label' => $doc->status_label,
                            'status_color' => $doc->status_color,
                            'catatan_verifikasi' => $doc->catatan_verifikasi,
                        ];
                    }),
                    'history' => $pernikahan->history->map(function ($hist) {
                        return [
                            'status_sebelum' => $hist->status_sebelum,
                            'status_setelah' => $hist->status_setelah,
                            'catatan' => $hist->catatan,
                            'created_at' => $hist->created_at->format('d-m-Y H:i'),
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pernikahan tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Cek status berdasarkan nomor antrian
     * GET /api/pernikahan/antrian/{nomor_antrian}
     */
    public function statusByNomorAntrian(string $nomorAntrian): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::where('nomor_antrian', $nomorAntrian)
                ->firstOrFail();

            return $this->status($pernikahan->pernikahan_id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor antrian tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Upload dokumen pernikahan
     * POST /api/pernikahan/{pernikahan_id}/upload-dokumen
     */
    public function uploadDokumen(Request $request, string $pernikahanId): JsonResponse
    {
        $request->validate([
            'jenis_dokumen' => 'required|string|in:surat_keterangan,ktp_mempelai_pria,ktp_mempelai_wanita,kk_mempelai_pria,kk_mempelai_wanita,surat_ijin_orang_tua,surat_n1_n2_n4,foto_prewedding,bukti_pembayaran,lainnya',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $pernikahanId)
                ->firstOrFail();

            if (!$pernikahan->canUploadDocuments()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengupload dokumen. Status atau deadline tidak memenuhi syarat.',
                ], 400);
            }

            DB::beginTransaction();

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $path = $file->storeAs('pernikahan/' . $pernikahanId, $filename, 'public');

            $dokumen = DokumenPernikahan::create([
                'pernikahan_id' => $pernikahanId,
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

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => [
                    'dokumen_id' => $dokumen->id,
                    'jenis_dokumen' => $dokumen->jenis_dokumen,
                    'jenis_dokumen_label' => $dokumen->jenis_dokumen_label,
                    'file_path' => Storage::url($path),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal upload dokumen pernikahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload dokumen',
                'error' => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan',
            ], 500);
        }
    }

    /**
     * Get list lembaga keagamaan
     * GET /api/pernikahan/keagamaan
     */
    public function listKeagamaan(Request $request): JsonResponse
    {
        try {
            $query = DB::table('keagamaan')
                ->join('users', 'keagamaan.user_id', '=', 'users.id')
                ->join('jenis_keagamaan', 'keagamaan.jenis_keagamaan_id', '=', 'jenis_keagamaan.jenis_keagamaan_id')
                ->select([
                    'keagamaan.keagamaan_id',
                    'users.id as user_id',
                    'users.name as nama_tempat',
                    'jenis_keagamaan.jenis_keagamaan_id',
                    'jenis_keagamaan.nama_jenis_keagamaan',
                    'keagamaan.alamat',
                ])
                ->where('keagamaan.status', 'aktif');

            // Filter by jenis agama if provided
            if ($request->has('jenis_keagamaan_id')) {
                $query->where('keagamaan.jenis_keagamaan_id', $request->jenis_keagamaan_id);
            }

            $keagamaan = $query->get();

            return response()->json([
                'success' => true,
                'data' => $keagamaan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lembaga keagamaan',
            ], 500);
        }
    }

    /**
     * Get list jenis agama
     * GET /api/pernikahan/jenis-agama
     */
    public function listJenisAgama(): JsonResponse
    {
        try {
            $jenisAgama = DB::table('jenis_keagamaan')
                ->orderBy('nama_jenis_keagamaan')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $jenisAgama,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jenis agama',
            ], 500);
        }
    }

    // =========================================================================
    // ADMIN API ENDPOINTS
    // =========================================================================

    /**
     * Get calendar data for admin dashboard
     * GET /api/pernikahan/admin/calendar
     */
    public function calendarData(Request $request): JsonResponse
    {
        try {
            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);

            $pernikahan = LayananPernikahan::where('status', LayananPernikahan::STATUS_TANGGAL_DISETUJUI)
                ->whereYear('tanggal_perkawinan', $year)
                ->whereMonth('tanggal_perkawinan', $month)
                ->select(['pernikahan_id', 'nomor_antrian', 'nama_mempelai_pria', 'tanggal_perkawinan', 'nama_gereja'])
                ->get();

            $calendarData = [];
            foreach ($pernikahan as $p) {
                $dateKey = $p->tanggal_perkawinan->format('Y-m-d');
                if (!isset($calendarData[$dateKey])) {
                    $calendarData[$dateKey] = [];
                }
                $calendarData[$dateKey][] = [
                    'id' => $p->pernikahan_id,
                    'nomor_antrian' => $p->nomor_antrian,
                    'nama_pria' => $p->nama_mempelai_pria,
                    'gereja' => $p->nama_gereja,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $calendarData,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal load calendar data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
            ]);
        }
    }

    /**
     * Get detail pernikahan for admin
     * GET /api/pernikahan/admin/detail/{id}
     */
    public function detail(string $id): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::with(['dokumen', 'user', 'history'])
                ->where('pernikahan_id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'tanggal_perkawinan' => $pernikahan->tanggal_perkawinan?->format('d M Y'),
                    'nama_gereja' => $pernikahan->nama_gereja,
                    'alasan_ditolak' => $pernikahan->alasan_ditolak,
                    'nama_mempelai_pria' => $pernikahan->nama_mempelai_pria,
                    'nik_mempelai_pria' => $pernikahan->nik_mempelai_pria,
                    'tempat_lahir_mempelai_pria' => $pernikahan->tempat_lahir_mempelai_pria,
                    'tanggal_lahir_mempelai_pria' => $pernikahan->tanggal_lahir_mempelai_pria?->format('d M Y'),
                    'agama_mempelai_pria' => $pernikahan->agama_mempelai_pria,
                    'pekerjaan_mempelai_pria' => $pernikahan->pekerjaan_mempelai_pria,
                    'alamat_mempelai_pria' => $pernikahan->alamat_mempelai_pria,
                    'nama_mempelai_wanita' => $pernikahan->nama_mempelai_wanita,
                    'nik_mempelai_wanita' => $pernikahan->nik_mempelai_wanita,
                    'tempat_lahir_mempelai_wanita' => $pernikahan->tempat_lahir_mempelai_wanita,
                    'tanggal_lahir_mempelai_wanita' => $pernikahan->tanggal_lahir_mempelai_wanita?->format('d M Y'),
                    'agama_mempelai_wanita' => $pernikahan->agama_mempelai_wanita,
                    'pekerjaan_mempelai_wanita' => $pernikahan->pekerjaan_mempelai_wanita,
                    'alamat_mempelai_wanita' => $pernikahan->alamat_mempelai_wanita,
                    // File KTP
                    'file_ktp_mempelai_pria' => $pernikahan->file_ktp_mempelai_pria ? Storage::url($pernikahan->file_ktp_mempelai_pria) : null,
                    'file_ktp_mempelai_wanita' => $pernikahan->file_ktp_mempelai_wanita ? Storage::url($pernikahan->file_ktp_mempelai_wanita) : null,
                    'file_ktp_saksi_1' => $pernikahan->file_ktp_saksi_1 ? Storage::url($pernikahan->file_ktp_saksi_1) : null,
                    'file_ktp_saksi_2' => $pernikahan->file_ktp_saksi_2 ? Storage::url($pernikahan->file_ktp_saksi_2) : null,
                    'nama_saksi_1' => $pernikahan->nama_saksi_1,
                    'nik_saksi_1' => $pernikahan->nik_saksi_1,
                    'nama_saksi_2' => $pernikahan->nama_saksi_2,
                    'nik_saksi_2' => $pernikahan->nik_saksi_2,
                    'dokumen' => $pernikahan->dokumen->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'jenis_dokumen' => $doc->jenis_dokumen,
                            'jenis_dokumen_label' => $doc->jenis_dokumen_label,
                            'status' => $doc->status,
                            'status_label' => $doc->status_label,
                            'file_path' => $doc->file_path,
                            'file_url' => $doc->file_path ? Storage::url($doc->file_path) : null,
                            'catatan_verifikasi' => $doc->catatan_verifikasi,
                        ];
                    }),
                    'file_berkas_acara' => $pernikahan->file_berkas_acara ? Storage::url($pernikahan->file_berkas_acara) : null,
                    'file_surat_keterangan' => $pernikahan->file_surat_keterangan ? Storage::url($pernikahan->file_surat_keterangan) : null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Approve tanggal perkawinan
     * POST /api/pernikahan/admin/{id}/approve
     */
    public function approveTanggal(Request $request, string $id): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->whereIn('status', [
                    LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    LayananPernikahan::STATUS_TANGGAL_DITOLAK,
                ])
                ->firstOrFail();

            DB::beginTransaction();

            $pernikahan->update([
                'status' => LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                'alasan_ditolak' => null,
            ]);

            AdminNotificationService::pernikahanVerifikasiAdmin(
                $pernikahan->pernikahan_id,
                'diterima',
                null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tanggal perkawinan disetujui',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal approve tanggal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui tanggal',
            ], 500);
        }
    }

    /**
     * Reject tanggal perkawinan
     * POST /api/pernikahan/admin/{id}/reject
     */
    public function rejectTanggal(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->whereIn('status', [
                    LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                ])
                ->firstOrFail();

            DB::beginTransaction();

            $pernikahan->update([
                'status' => LayananPernikahan::STATUS_TANGGAL_DITOLAK,
                'alasan_ditolak' => $request->alasan,
            ]);

            AdminNotificationService::pernikahanVerifikasiAdmin(
                $pernikahan->pernikahan_id,
                'ditolak',
                $request->alasan
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tanggal perkawinan ditolak',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal reject tanggal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak tanggal',
            ], 500);
        }
    }

    /**
     * Reject dokumen (memerlukan perbaikan)
     * POST /api/pernikahan/admin/{id}/reject-doc
     */
    public function rejectDokumen(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->where('status', LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI)
                ->firstOrFail();

            DB::beginTransaction();

            $pernikahan->update([
                'status' => LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                'alasan_ditolak' => $request->alasan,
            ]);

            // Update all documents to need revision
            DokumenPernikahan::where('pernikahan_id', $id)
                ->where('status', DokumenPernikahan::STATUS_UPLOADED)
                ->update([
                    'status' => DokumenPernikahan::STATUS_PERLU_PERBAIKAN,
                    'catatan_verifikasi' => $request->alasan,
                ]);

            AdminNotificationService::pernikahanVerifikasiAdmin(
                $pernikahan->pernikahan_id,
                'perlu_perbaikan',
                $request->alasan
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen ditolak, perlu perbaikan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal reject dokumen: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak dokumen',
            ], 500);
        }
    }

    /**
     * Verify all documents
     * POST /api/pernikahan/admin/{id}/verify-all
     */
    public function verifyAll(Request $request, string $id): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->where('status', LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI)
                ->firstOrFail();

            DB::beginTransaction();

            // Update all documents to verified
            DokumenPernikahan::where('pernikahan_id', $id)
                ->whereIn('status', [
                    DokumenPernikahan::STATUS_UPLOADED,
                    DokumenPernikahan::STATUS_PERLU_PERBAIKAN,
                ])
                ->update(['status' => DokumenPernikahan::STATUS_VERIFIED]);

            $pernikahan->update([
                'status' => LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
            ]);

            AdminNotificationService::pernikahanVerifikasiAdmin(
                $pernikahan->pernikahan_id,
                'dokumen_diverifikasi',
                null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua dokumen diverifikasi',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal verify all: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi dokumen',
            ], 500);
        }
    }

    /**
     * Upload berkas pernikahan (berkas acara & surat keterangan)
     * POST /api/pernikahan/admin/{id}/upload-berkas
     */
    public function uploadBerkas(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:2048',
            'type' => 'required|in:berkas_acara,surat_keterangan',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->where('status', LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI)
                ->firstOrFail();

            DB::beginTransaction();

            $file = $request->file('file');
            $type = $request->type;
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pernikahan/' . $id . '/berkas', $filename, 'public');

            if ($type === 'berkas_acara') {
                $pernikahan->file_berkas_acara = $path;
            } else {
                $pernikahan->file_surat_keterangan = $path;
            }

            // Check if both files are uploaded
            if ($pernikahan->file_berkas_acara && $pernikahan->file_surat_keterangan) {
                $pernikahan->status = LayananPernikahan::STATUS_SELESAI;

                AdminNotificationService::pernikahanSelesai(
                    $pernikahan->pernikahan_id,
                    $pernikahan->nomor_antrian
                );
            }

            $pernikahan->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berkas berhasil diupload',
                'data' => [
                    'file_url' => Storage::url($path),
                    'status' => $pernikahan->status,
                    'is_complete' => $pernikahan->file_berkas_acara && $pernikahan->file_surat_keterangan,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal upload berkas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload berkas',
            ], 500);
        }
    }
}
