<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifikasiDokumenRequest;
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
    public function __construct()
    {
        // Hanya butuh autentikasi, role dicek di route level jika diperlukan
        $this->middleware(['auth']);
    }

    /**
     * Dashboard pernikahan - list semua permohonan
     * GET /admin/pernikahan
     */
    public function index(Request $request)
    {
        $query = LayananPernikahan::with(['dokumen', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_antrian', 'like', "%{$search}%")
                    ->orWhere('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('nik_pemohon', 'like', "%{$search}%")
                    ->orWhere('nik_mempelai_pria', 'like', "%{$search}%")
                    ->orWhere('nik_mempelai_wanita', 'like', "%{$search}%");
            });
        }

        $pernikahan = $query->paginate(20);

        $statusList = [
            LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
            LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
            LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
            LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
            LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
            LayananPernikahan::STATUS_SELESAI,
            LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
            LayananPernikahan::STATUS_TANGGAL_DITOLAK,
        ];

        $statistics = [
            'total' => LayananPernikahan::count(),
            'menunggu_konfirmasi' => LayananPernikahan::menungguKonfirmasiKeagamaan()->count(),
            'menunggu_approve' => LayananPernikahan::menungguApproveTanggal()->count(),
            'menunggu_verifikasi' => LayananPernikahan::menungguVerifikasiDokumen()->count(),
            'selesai' => LayananPernikahan::where('status', LayananPernikahan::STATUS_SELESAI)->count(),
        ];

        return view('admin.pernikahan', compact('pernikahan', 'statusList', 'statistics'));
    }

    /**
     * Detail pernikahan
     * GET /admin/pernikahan/{id}
     */
    public function show(string $id)
    {
        $pernikahan = LayananPernikahan::with(['dokumen', 'history', 'user'])
            ->where('pernikahan_id', $id)
            ->firstOrFail();

        $jenisDokumen = DokumenPernikahan::JENIS_DOKUMEN;

        // Return JSON jika request AJAX
        if (request()->ajax() || request()->wantsJson()) {
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
                    'file_ktp_mempelai_pria' => $pernikahan->file_ktp_mempelai_pria,
                    'file_ktp_mempelai_wanita' => $pernikahan->file_ktp_mempelai_wanita,
                    'file_ktp_saksi_1' => $pernikahan->file_ktp_saksi_1,
                    'file_ktp_saksi_2' => $pernikahan->file_ktp_saksi_2,
                ],
            ]);
        }

        $jenisDokumen = DokumenPernikahan::JENIS_DOKUMEN;

        return view('admin.pernikahan-detail', compact('pernikahan', 'jenisDokumen'));
    }

    /**
     * Detail AJAX untuk modal
     * POST /admin/pernikahan/detail-ajax
     */
    public function detailAjax(Request $request): JsonResponse
    {
        $request->validate([
            'pernikahan_id' => 'required|string',
        ]);

        try {
            $pernikahan = LayananPernikahan::with(['dokumen'])
                ->where('pernikahan_id', $request->pernikahan_id)
                ->firstOrFail();

            // Tentukan warna status badge
            $statusColor = 'bg-gray-100 text-gray-800';
            if (in_array($pernikahan->status, [
                LayananPernikahan::STATUS_SELESAI,
            ])) {
                $statusColor = 'bg-green-100 text-green-800';
            } elseif (in_array($pernikahan->status, [
                LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
            ])) {
                $statusColor = 'bg-blue-100 text-blue-800';
            } elseif (in_array($pernikahan->status, [
                LayananPernikahan::STATUS_TANGGAL_DITOLAK,
                LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
            ])) {
                $statusColor = 'bg-red-100 text-red-800';
            } elseif ($pernikahan->status === LayananPernikahan::STATUS_TANGGAL_DISETUJUI) {
                $statusColor = 'bg-yellow-100 text-yellow-800';
            }

            // Kumpulkan file KTP (hanya untuk status sebelum verifikasi dokumen)
            $ktpFiles = [];
            if ($pernikahan->file_ktp_mempelai_pria) {
                $ktpFiles['mempelai_pria'] = Storage::url($pernikahan->file_ktp_mempelai_pria);
            }
            if ($pernikahan->file_ktp_mempelai_wanita) {
                $ktpFiles['mempelai_wanita'] = Storage::url($pernikahan->file_ktp_mempelai_wanita);
            }
            if ($pernikahan->file_ktp_saksi_1) {
                $ktpFiles['saksi_1'] = Storage::url($pernikahan->file_ktp_saksi_1);
            }
            if ($pernikahan->file_ktp_saksi_2) {
                $ktpFiles['saksi_2'] = Storage::url($pernikahan->file_ktp_saksi_2);
            }

            // Kumpulkan dokumen dari keagamaan (untuk status verifikasi dokumen)
            $dokumenKeagamaan = [];
            if (in_array($pernikahan->status, [
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                LayananPernikahan::STATUS_SELESAI,
            ])) {
                foreach ($pernikahan->dokumen as $doc) {
                    $dokumenKeagamaan[] = [
                        'id' => $doc->id,
                        'jenis_dokumen' => $doc->jenis_dokumen,
                        'jenis_dokumen_label' => $doc->jenis_dokumen_label,
                        'status' => $doc->status,
                        'status_label' => $doc->status_label,
                        'file_url' => $doc->file_path ? Storage::url($doc->file_path) : null,
                        'original_filename' => $doc->original_filename,
                        'catatan_verifikasi' => $doc->catatan_verifikasi,
                    ];
                }
            }

            // Tentukan apakah bisa dikonfirmasi
            $canKonfirmasi = in_array($pernikahan->status, [
                LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'status_color' => $statusColor,
                    'tanggal_perkawinan' => $pernikahan->tanggal_perkawinan?->format('d M Y'),
                    'tanggal_perkawinan_raw' => $pernikahan->tanggal_perkawinan?->format('Y-m-d'),
                    'nama_gereja' => $pernikahan->nama_gereja,
                    'alasan_ditolak' => $pernikahan->alasan_ditolak,
                    'catatan_keagamaan' => $pernikahan->catatan_keagamaan,
                    'catatan_admin' => $pernikahan->catatan_admin,
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
                    'nama_pemohon' => $pernikahan->nama_pemohon,
                    'nik_pemohon' => $pernikahan->nik_pemohon,
                    'alamat_pemohon' => $pernikahan->alamat_pemohon,
                    'nama_saksi_1' => $pernikahan->nama_saksi_1,
                    'nama_saksi_2' => $pernikahan->nama_saksi_2,
                    'ktp_files' => $ktpFiles,
                    'dokumen_keagamaan' => $dokumenKeagamaan,
                    'can_konfirmasi' => $canKonfirmasi,
                    // Dokumen final hasil penerbitan Disdukcapil (URL bersih via route)
                    'dokumen_final' => [
                        'akta_pernikahan' => $pernikahan->file_akta_pernikahan
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'akta_pernikahan'])
                            : null,
                        'kk_pasangan' => $pernikahan->file_kk_pasangan
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'kk_pasangan'])
                            : null,
                        'kk_ortu_pria' => $pernikahan->file_kk_ortu_pria
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'kk_ortu_pria'])
                            : null,
                        'kk_ortu_wanita' => $pernikahan->file_kk_ortu_wanita
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'kk_ortu_wanita'])
                            : null,
                        'uploaded_at' => $pernikahan->dokumen_final_uploaded_at?->format('d M Y H:i'),
                    ],
                    'can_upload_dokumen_final' => $pernikahan->status === LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI
                        || $pernikahan->status === LayananPernikahan::STATUS_SELESAI,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail pernikahan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pernikahan',
            ], 500);
        }
    }

    /**
     * Approve tanggal perkawinan
     * POST /admin/pernikahan/{id}/approve-tanggal
     */
    public function approveTanggal(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

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
                'catatan_admin' => $request->catatan,
            ]);

            AdminNotificationService::pernikahanVerifikasiAdmin(
                $pernikahan->pernikahan_id,
                'diterima',
                $request->catatan
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tanggal perkawinan disetujui',
                'data' => [
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                ],
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
     * POST /admin/pernikahan/{id}/reject-tanggal
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
                'catatan_admin' => $request->alasan,
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
                'data' => [
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                ],
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
     * Verifikasi dokumen
     * POST /admin/pernikahan/{id}/verifikasi
     */
    public function verifikasi(VerifikasiDokumenRequest $request, string $id): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->where('status', LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI)
                ->firstOrFail();

            DB::beginTransaction();

            $allApproved = true;
            $hasRejected = false;

            foreach ($request->dokumen_id as $index => $dokumenId) {
                $dokumen = DokumenPernikahan::where('id', $dokumenId)
                    ->where('pernikahan_id', $id)
                    ->firstOrFail();

                $status = $request->status[$index];
                $catatan = $request->catatan[$index] ?? null;

                $dokumen->update([
                    'status' => $status,
                    'catatan_verifikasi' => $catatan,
                ]);

                if ($status === DokumenPernikahan::STATUS_DITOLAK) {
                    $hasRejected = true;
                }
            }

            if ($hasRejected) {
                $pernikahan->update([
                    'status' => LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                ]);
            } else {
                $pernikahan->update([
                    'status' => LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                ]);
            }

            AdminNotificationService::pernikahanVerifikasiAdmin(
                $pernikahan->pernikahan_id,
                $hasRejected ? 'perlu_perbaikan' : 'dokumen_diverifikasi'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $hasRejected
                    ? 'Dokumen perlu perbaikan'
                    : 'Semua dokumen diverifikasi',
                'data' => [
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal verifikasi dokumen: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi dokumen',
            ], 500);
        }
    }

    /**
     * Upload berkas acara/surat keterangan
     * POST /admin/pernikahan/{id}/upload-berkas
     */
    public function uploadBerkas(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'file_berkas_acara' => 'nullable|file|mimes:pdf|max:2048',
            'file_surat_keterangan' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->where('status', LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI)
                ->firstOrFail();

            DB::beginTransaction();

            if ($request->hasFile('file_berkas_acara')) {
                $file = $request->file('file_berkas_acara');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pernikahan/' . $id . '/berkas', $filename, 'public');
                $pernikahan->file_berkas_acara = $path;
            }

            if ($request->hasFile('file_surat_keterangan')) {
                $file = $request->file('file_surat_keterangan');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pernikahan/' . $id . '/berkas', $filename, 'public');
                $pernikahan->file_surat_keterangan = $path;
            }

            if ($pernikahan->file_berkas_acara && $pernikahan->file_surat_keterangan) {
                $pernikahan->status = LayananPernikahan::STATUS_SELESAI;
            }

            $pernikahan->save();

            AdminNotificationService::pernikahanSelesai(
                $pernikahan->pernikahan_id,
                $pernikahan->nomor_antrian
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berkas berhasil diupload',
                'data' => [
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'file_berkas_acara' => $pernikahan->file_berkas_acara ? Storage::url($pernikahan->file_berkas_acara) : null,
                    'file_surat_keterangan' => $pernikahan->file_surat_keterangan ? Storage::url($pernikahan->file_surat_keterangan) : null,
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

    /**
     * Upload dokumen final hasil penerbitan Disdukcapil
     * (Akta Pernikahan, KK pasangan baru, KK ortu pria, KK ortu wanita)
     * POST /admin/pernikahan/{id}/upload-dokumen-final
     */
    public function uploadDokumenFinal(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'file_akta_pernikahan' => 'nullable|file|mimes:pdf|max:2048',
            'file_kk_pasangan' => 'nullable|file|mimes:pdf|max:2048',
            'file_kk_ortu_pria' => 'nullable|file|mimes:pdf|max:2048',
            'file_kk_ortu_wanita' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->whereIn('status', [
                    LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                    LayananPernikahan::STATUS_SELESAI,
                ])
                ->firstOrFail();

            $fieldMap = [
                'file_akta_pernikahan' => 'akta_pernikahan',
                'file_kk_pasangan' => 'kk_pasangan',
                'file_kk_ortu_pria' => 'kk_ortu_pria',
                'file_kk_ortu_wanita' => 'kk_ortu_wanita',
            ];

            DB::beginTransaction();

            $uploaded = 0;

            foreach ($fieldMap as $field => $slug) {
                if (! $request->hasFile($field)) {
                    continue;
                }

                // Hapus file lama bila ada
                if (! empty($pernikahan->{$field}) && Storage::disk('public')->exists($pernikahan->{$field})) {
                    Storage::disk('public')->delete($pernikahan->{$field});
                }

                $file = $request->file($field);
                $filename = $slug . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pernikahan/' . $id . '/dokumen-final', $filename, 'public');

                $pernikahan->{$field} = $path;
                $uploaded++;
            }

            if ($uploaded === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada file yang diupload',
                ], 422);
            }

            $pernikahan->dokumen_final_uploaded_at = now();

            // Jika ke-4 dokumen sudah lengkap, tandai SELESAI
            $isComplete = $pernikahan->file_akta_pernikahan
                && $pernikahan->file_kk_pasangan
                && $pernikahan->file_kk_ortu_pria
                && $pernikahan->file_kk_ortu_wanita;

            if ($isComplete && $pernikahan->status !== LayananPernikahan::STATUS_SELESAI) {
                $pernikahan->status = LayananPernikahan::STATUS_SELESAI;
            }

            $pernikahan->save();

            if ($isComplete) {
                try {
                    AdminNotificationService::pernikahanSelesai(
                        $pernikahan->pernikahan_id,
                        $pernikahan->nomor_antrian
                    );
                } catch (\Throwable $e) {
                    Log::warning('Notifikasi selesai gagal dikirim: ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isComplete
                    ? 'Semua dokumen final tersimpan. Status diubah menjadi Selesai.'
                    : "Berhasil mengupload {$uploaded} dokumen.",
                'data' => [
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'is_complete' => $isComplete,
                    'dokumen_final' => [
                        'akta_pernikahan' => $pernikahan->file_akta_pernikahan
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'akta_pernikahan'])
                            : null,
                        'kk_pasangan' => $pernikahan->file_kk_pasangan
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'kk_pasangan'])
                            : null,
                        'kk_ortu_pria' => $pernikahan->file_kk_ortu_pria
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'kk_ortu_pria'])
                            : null,
                        'kk_ortu_wanita' => $pernikahan->file_kk_ortu_wanita
                            ? route('antrian.dokumen-final', ['pernikahanId' => $pernikahan->pernikahan_id, 'jenis' => 'kk_ortu_wanita'])
                            : null,
                        'uploaded_at' => $pernikahan->dokumen_final_uploaded_at?->format('d M Y H:i'),
                    ],
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan tidak ditemukan atau status belum diverifikasi',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal upload dokumen final: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload dokumen final',
            ], 500);
        }
    }

    /**
     * Get data untuk datatable
     * GET /admin/pernikahan/data
     */
    public function getData(Request $request): JsonResponse
    {
        $query = LayananPernikahan::with(['dokumen', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_antrian', 'like', "%{$search}%")
                    ->orWhere('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('nik_pemohon', 'like', "%{$search}%");
            });
        }

        $pernikahan = $query->paginate($request->length ?? 20);

        return response()->json([
            'success' => true,
            'data' => $pernikahan->items(),
            'recordsTotal' => $pernikahan->total(),
            'recordsFiltered' => $pernikahan->total(),
        ]);
    }

    /**
     * Get calendar data untuk admin dashboard
     * GET /admin/pernikahan/calendar-data
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
}
