<?php

namespace App\Http\Controllers\Keagamaan;

use App\Http\Controllers\Controller;
use App\Http\Requests\KonfirmasiJemaatRequest;
use App\Http\Requests\UploadDokumenRequest;
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
        $this->middleware(['auth']);
    }

    /**
     * Dashboard pernikahan untuk keagamaan
     * GET /keagamaan/pernikahan
     */
    public function index(Request $request)
    {
        // Cari keagamaan_id milik user yang sedang login
        $keagamaan = DB::table('keagamaan')
            ->where('user_id', auth()->id())
            ->first();

        if (!$keagamaan) {
            abort(403, 'Akun ini tidak terhubung ke data keagamaan.');
        }

        $keagamaanId = $keagamaan->keagamaan_id;

        $query = LayananPernikahan::with(['dokumen'])
            ->where('keagamaan_id', $keagamaanId) // ← hanya data milik gereja ini
            ->whereIn('status', [
                LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
                LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                LayananPernikahan::STATUS_SELESAI,
            ])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_antrian', 'like', "%{$search}%")
                    ->orWhere('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('nama_gereja', 'like', "%{$search}%");
            });
        }

        $pernikahan = $query->paginate(20);

        $statistics = [
            'menunggu_konfirmasi' => LayananPernikahan::where('keagamaan_id', $keagamaanId)
                ->menungguKonfirmasiKeagamaan()->count(),
            'dalam_proses' => LayananPernikahan::where('keagamaan_id', $keagamaanId)
                ->whereIn('status', [
                    LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                    LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                    LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                ])->count(),
            'tanggal_disetujui' => LayananPernikahan::where('keagamaan_id', $keagamaanId)
                ->where('status', LayananPernikahan::STATUS_TANGGAL_DISETUJUI)->count(),
            'selesai' => LayananPernikahan::where('keagamaan_id', $keagamaanId)
                ->where('status', LayananPernikahan::STATUS_SELESAI)->count(),
        ];

        return view('keagamaan.pernikahan', compact('pernikahan', 'statistics'));
    }

    /**
     * Halaman Request Tanggal ke Disdukcapil
     * GET /keagamaan/pernikahan/request-tanggal
     */
    public function requestTanggal(Request $request)
    {
        $query = LayananPernikahan::with(['dokumen'])
            ->whereIn('status', [
                LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                LayananPernikahan::STATUS_TANGGAL_DITOLAK,
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
            ])
            ->orderBy('created_at', 'desc');

        $requestTanggal = $query->get()->map(function ($item) {
            // Tentukan request_status berdasarkan status aktual
            if ($item->status === LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL) {
                $item->request_status = 'pending';
            } elseif ($item->status === LayananPernikahan::STATUS_TANGGAL_DITOLAK) {
                $item->request_status = 'rejected';
            } elseif ($item->status === LayananPernikahan::STATUS_TANGGAL_DISETUJUI) {
                $item->request_status = 'approved';
            } elseif (in_array($item->status, [
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                LayananPernikahan::STATUS_SELESAI,
            ])) {
                $item->request_status = 'approved';
            } else {
                $item->request_status = 'pending';
            }
            return $item;
        });

        // Ambil data jemaat yang tersedia untuk modal request tanggal baru
        // Hanya yang statusnya TANGGAL_DISETUJUI dan punya tanggal perkawinan
        $availableJemaat = LayananPernikahan::where('status', LayananPernikahan::STATUS_TANGGAL_DISETUJUI)
            ->whereNotNull('tanggal_perkawinan')
            ->orderBy('tanggal_perkawinan', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'pernikahan_id' => $item->pernikahan_id,
                    'nomor_antrian' => $item->nomor_antrian,
                    'nama_mempelai_pria' => $item->nama_mempelai_pria,
                    'nama_mempelai_wanita' => $item->nama_mempelai_wanita,
                    'tanggal_perkawinan' => $item->tanggal_perkawinan?->format('d F Y'),
                    'nama_gereja' => $item->nama_gereja,
                ];
            });

        return view('keagamaan.request-tanggal', compact('requestTanggal', 'availableJemaat'));
    }

    /**
     * Halaman Upload Berkas
     * GET /keagamaan/pernikahan/upload-berkas
     */
    public function uploadBerkas(Request $request)
    {
        $query = LayananPernikahan::with(['dokumen'])
            ->whereIn('status', [
                LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
            ])
            ->orderBy('created_at', 'desc');

        $uploadBerkas = $query->paginate(20);

        return view('keagamaan.upload-berkas', compact('uploadBerkas'));
    }

    /**
     * Handle Upload Berkas
     * POST /keagamaan/pernikahan/upload-berkas-post
     */
    public function uploadBerkasPost(Request $request): JsonResponse
    {
        $request->validate([
            'pernikahan_id' => 'required|string|exists:layanan_pernikahan,pernikahan_id',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $request->pernikahan_id)
                ->whereIn('status', [
                    LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                    LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                    LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                ])
                ->firstOrFail();

            DB::beginTransaction();

            $dokumenTypes = [
                'surat_keterangan_agama' => 'surat_keterangan',
                'ktp_mempelai' => 'ktp_mempelai',
                'kk_orang_tua' => 'kartu_keluarga',
                'ktp_saksi' => 'ktp_saksi',
                'akta_kematian' => 'akta_kematian',
                'akta_perceraian' => 'akta_perceraian',
            ];

            $uploadedCount = 0;

            foreach ($dokumenTypes as $field => $jenisDokumen) {
                if ($request->hasFile($field)) {
                    // Hapus dokumen lama dengan jenis yang sama
                    $oldDocuments = DokumenPernikahan::where('pernikahan_id', $request->pernikahan_id)
                        ->where('jenis_dokumen', $jenisDokumen)
                        ->get();

                    foreach ($oldDocuments as $oldDoc) {
                        // Hapus file fisik jika ada
                        if ($oldDoc->file_path && Storage::disk('public')->exists($oldDoc->file_path)) {
                            Storage::disk('public')->delete($oldDoc->file_path);
                        }
                        $oldDoc->delete();
                    }

                    $files = $request->file($field);
                    if (!is_array($files)) {
                        $files = [$files];
                    }

                    foreach ($files as $file) {
                        if ($file->isValid()) {
                            $extension = $file->getClientOriginalExtension();
                            $filename = Str::uuid() . '.' . $extension;
                            $path = $file->storeAs('pernikahan/' . $request->pernikahan_id, $filename, 'public');

                            DokumenPernikahan::create([
                                'pernikahan_id' => $request->pernikahan_id,
                                'jenis_dokumen' => $jenisDokumen,
                                'file_path' => $path,
                                'original_filename' => $file->getClientOriginalName(),
                                'mime_type' => $file->getMimeType(),
                                'file_size' => $file->getSize(),
                                'status' => DokumenPernikahan::STATUS_UPLOADED,
                            ]);

                            $uploadedCount++;
                        }
                    }
                }
            }

            // Update status jika semua dokumen sudah diupload
            if ($uploadedCount > 0) {
                $pernikahan->status = LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI;
                $pernikahan->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengupload {$uploadedCount} dokumen",
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
     * Get Detail via AJAX
     * POST /keagamaan/pernikahan/detail-ajax
     */
    public function detailAjax(Request $request): JsonResponse
    {
        $request->validate([
            'pernikahan_id' => 'required|string|exists:layanan_pernikahan,pernikahan_id',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $request->pernikahan_id)->firstOrFail();

            $statusColor = match ($pernikahan->status) {
                LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
                LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL => 'bg-yellow-100 text-yellow-700',
                LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
                LayananPernikahan::STATUS_TANGGAL_DITOLAK => 'bg-red-100 text-red-700',
                LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                LayananPernikahan::STATUS_SELESAI => 'bg-green-100 text-green-700',
                default => 'bg-gray-100 text-gray-700',
            };

            // File KTP URLs - gunakan asset() untuk full URL
            $ktpFiles = [
                'mempelai_pria' => $pernikahan->file_ktp_mempelai_pria ? asset(Storage::url($pernikahan->file_ktp_mempelai_pria)) : null,
                'mempelai_wanita' => $pernikahan->file_ktp_mempelai_wanita ? asset(Storage::url($pernikahan->file_ktp_mempelai_wanita)) : null,
                'saksi_1' => $pernikahan->file_ktp_saksi_1 ? asset(Storage::url($pernikahan->file_ktp_saksi_1)) : null,
                'saksi_2' => $pernikahan->file_ktp_saksi_2 ? asset(Storage::url($pernikahan->file_ktp_saksi_2)) : null,
            ];

            // Dokumen final hasil penerbitan Disdukcapil
            $dokumenFinal = [
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
                'uploaded_at' => $pernikahan->dokumen_final_uploaded_at
                    ? $pernikahan->dokumen_final_uploaded_at->format('d M Y H:i')
                    : null,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'nama_pemohon' => $pernikahan->nama_pemohon,
                    'nik_pemohon' => $pernikahan->nik_pemohon,
                    'alamat_pemohon' => $pernikahan->alamat_pemohon,
                    'nama_mempelai_pria' => $pernikahan->nama_mempelai_pria,
                    'nama_mempelai_wanita' => $pernikahan->nama_mempelai_wanita,
                    'nik_mempelai_pria' => $pernikahan->nik_mempelai_pria,
                    'nik_mempelai_wanita' => $pernikahan->nik_mempelai_wanita,
                    'nama_gereja' => $pernikahan->nama_gereja,
                    'tanggal_perkawinan' => $pernikahan->tanggal_perkawinan?->format('d F Y'),
                    'tanggal_perkawinan_raw' => $pernikahan->tanggal_perkawinan?->format('Y-m-d'),
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'status_color' => $statusColor,
                    'catatan_keagamaan' => $pernikahan->catatan_keagamaan,
                    'alasan_ditolak' => $pernikahan->alasan_ditolak,
                    'ktp_files' => $ktpFiles,
                    'dokumen_final' => $dokumenFinal,
                    'can_konfirmasi' => $pernikahan->status === LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
                    'can_upload_berkas' => $pernikahan->status !== LayananPernikahan::STATUS_SELESAI
                        && in_array($pernikahan->status, [
                            LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                            LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                            LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                        ], true),
                    'upload_url' => route('keagamaan.pernikahan.upload-berkas'),
                    'detail_url' => route('keagamaan.pernikahan.show', $pernikahan->pernikahan_id),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail',
            ], 500);
        }
    }

    /**
     * Print Berkas
     * GET /keagamaan/pernikahan/print-berkas/{id}
     */
    public function printBerkas(string $id)
    {
        try {
            $pernikahan = LayananPernikahan::with(['dokumen'])
                ->where('pernikahan_id', $id)
                ->firstOrFail();

            // Generate PDF atau return view untuk print
            return view('keagamaan.print-berkas', compact('pernikahan'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Berkas tidak ditemukan');
        }
    }

    /**
     * Detail pernikahan untuk keagamaan
     * GET /keagamaan/pernikahan/{id}
     */
    public function show(string $id)
    {
        $pernikahan = LayananPernikahan::with(['dokumen', 'history', 'user'])
            ->where('pernikahan_id', $id)
            ->firstOrFail();

        $jenisDokumen = DokumenPernikahan::JENIS_DOKUMEN;

        return view('keagamaan.pernikahan-detail', compact('pernikahan', 'jenisDokumen'));
    }

    /**
     * Konfirmasi jemaat/gereja
     * POST /keagamaan/pernikahan/{id}/konfirmasi-jemaat
     */
    public function konfirmasiJemaat(KonfirmasiJemaatRequest $request, string $id): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->where('status', LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN)
                ->firstOrFail();

            DB::beginTransaction();

            if ($request->status === 'ditolak') {
                $pernikahan->update([
                    'status' => LayananPernikahan::STATUS_DITOLAK_KEAGAMAAN,
                    'alasan_ditolak' => $request->catatan,
                    'catatan_keagamaan' => $request->catatan,
                ]);

                AdminNotificationService::pernikahanVerifikasiKeagamaan(
                    $pernikahan->pernikahan_id,
                    'ditolak',
                    $request->catatan
                );
            } else {
                $pernikahan->update([
                    'status' => LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    'catatan_keagamaan' => $request->catatan,
                ]);

                if ($request->tanggal_perkawinan) {
                    $pernikahan->tanggal_perkawinan = $request->tanggal_perkawinan;
                }
                $pernikahan->save();

                AdminNotificationService::pernikahanVerifikasiKeagamaan(
                    $pernikahan->pernikahan_id,
                    'diterima',
                    $request->catatan
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->status === 'ditolak'
                    ? 'Permohonan ditolak'
                    : 'Jemaat dikonfirmasi',
                'data' => [
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal konfirmasi jemaat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkonfirmasi jemaat',
            ], 500);
        }
    }

    /**
     * Tentukan tanggal perkawinan
     * POST /keagamaan/pernikahan/{id}/set-tanggal
     */
    public function setTanggal(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'tanggal_perkawinan' => 'required|date|after:today',
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
                'tanggal_perkawinan' => $request->tanggal_perkawinan,
                'catatan_keagamaan' => $request->catatan,
                'status' => LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tanggal perkawinan disetujui',
                'data' => [
                    'status' => $pernikahan->status,
                    'status_label' => $pernikahan->status_label,
                    'tanggal_perkawinan' => $pernikahan->tanggal_perkawinan->format('d-m-Y'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal set tanggal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menetapkan tanggal',
            ], 500);
        }
    }

    /**
     * Upload dokumen dari pihak keagamaan
     * POST /keagamaan/pernikahan/{id}/upload-dokumen
     */
    public function uploadDokumen(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'jenis_dokumen' => 'required|string|in:surat_keterangan,ktp_mempelai_pria,ktp_mempelai_wanita,kk_mempelai_pria,kk_mempelai_wanita,surat_ijin_orang_tua,surat_n1_n2_n4,foto_prewedding,bukti_pembayaran,lainnya',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $id)
                ->firstOrFail();

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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => [
                    'file_path' => Storage::url($path),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal upload dokumen keagamaan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload dokumen',
            ], 500);
        }
    }

    /**
     * Get data untuk datatable
     * GET /keagamaan/pernikahan/data
     */
    public function getData(Request $request): JsonResponse
    {
        $query = LayananPernikahan::with(['dokumen'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status) {
            if ($request->status === 'all') {
                $query->whereIn('status', [
                    LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
                    LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                    LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                    LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                ]);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_antrian', 'like', "%{$search}%")
                    ->orWhere('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('nama_gereja', 'like', "%{$search}%");
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
     * Get jemaat yang tersedia untuk request tanggal
     * GET /keagamaan/pernikahan/available-jemaat
     */
    public function getAvailableJemaat(Request $request): JsonResponse
    {
        try {
            $jemaatList = LayananPernikahan::where('status', LayananPernikahan::STATUS_TANGGAL_DISETUJUI)
                ->whereNotNull('tanggal_perkawinan')
                ->orderBy('tanggal_perkawinan', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'pernikahan_id' => $item->pernikahan_id,
                        'nomor_antrian' => $item->nomor_antrian,
                        'nama_mempelai_pria' => $item->nama_mempelai_pria,
                        'nama_mempelai_wanita' => $item->nama_mempelai_wanita,
                        'tanggal_perkawinan' => $item->tanggal_perkawinan?->format('d F Y'),
                        'tanggal_perkawinan_raw' => $item->tanggal_perkawinan?->format('Y-m-d'),
                        'nama_gereja' => $item->nama_gereja,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $jemaatList,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data jemaat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jemaat',
            ], 500);
        }
    }

    /**
     * Submit request tanggal baru
     * POST /keagamaan/pernikahan/submit-request-tanggal
     */
    public function submitRequestTanggal(Request $request): JsonResponse
    {
        $request->validate([
            'pernikahan_id' => 'required|string|exists:layanan_pernikahan,pernikahan_id',
            'ack_jemaat' => 'accepted',
        ]);

        try {
            $pernikahan = LayananPernikahan::where('pernikahan_id', $request->pernikahan_id)
                ->where('status', LayananPernikahan::STATUS_TANGGAL_DISETUJUI)
                ->firstOrFail();

            // Di sini bisa ditambahkan logika untuk mengirim notifikasi ke admin/disdukcapil
            // Untuk sekarang, kita hanya mengembalikan respons sukses

            Log::info('Request tanggal dikirim', [
                'pernikahan_id' => $request->pernikahan_id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request tanggal berhasil dikirim ke Disdukcapil',
                'data' => [
                    'pernikahan_id' => $pernikahan->pernikahan_id,
                    'nomor_antrian' => $pernikahan->nomor_antrian,
                    'nama_mempelai_pria' => $pernikahan->nama_mempelai_pria,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal submit request tanggal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim request tanggal',
            ], 500);
        }
    }

    /**
     * Check untuk update status pernikahan (auto-refresh)
     * GET /keagamaan/pernikahan/check-updates
     */
    public function checkUpdates(Request $request): JsonResponse
    {
        try {
            $lastCheck = $request->get('last_check');
            $lastCheckTime = $lastCheck ? \Carbon\Carbon::parse($lastCheck) : now()->subMinutes(5);

            // Ambil semua pernikahan dengan status Menunggu Approve Tanggal
            $pernikahanList = LayananPernikahan::select(['pernikahan_id', 'status', 'nomor_antrian', 'nama_mempelai_pria', 'tanggal_perkawinan', 'nama_gereja', 'updated_at'])
                ->whereIn('status', [
                    LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                    LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                    LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                    LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                ])
                ->where('updated_at', '>=', $lastCheckTime)
                ->orderBy('updated_at', 'desc')
                ->get();

            $hasUpdates = $pernikahanList->count() > 0;

            return response()->json([
                'success' => true,
                'has_updates' => $hasUpdates,
                'updates' => $pernikahanList->map(function ($p) {
                    $statusLabel = '';
                    $statusColor = '';
                    switch ($p->status) {
                        case LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL:
                            $statusLabel = 'Menunggu';
                            $statusColor = 'blue';
                            break;
                        case LayananPernikahan::STATUS_TANGGAL_DISETUJUI:
                            $statusLabel = 'Disetujui';
                            $statusColor = 'green';
                            break;
                        case LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI:
                            $statusLabel = 'Verifikasi Dokumen';
                            $statusColor = 'purple';
                            break;
                        case LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN:
                            $statusLabel = 'Perlu Perbaikan';
                            $statusColor = 'orange';
                            break;
                        case LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI:
                            $statusLabel = 'Dokumen OK';
                            $statusColor = 'teal';
                            break;
                        case LayananPernikahan::STATUS_SELESAI:
                            $statusLabel = 'Selesai';
                            $statusColor = 'green';
                            break;
                    }

                    return [
                        'pernikahan_id' => $p->pernikahan_id,
                        'nomor_antrian' => $p->nomor_antrian,
                        'nama_mempelai_pria' => $p->nama_mempelai_pria,
                        'status' => $p->status,
                        'status_label' => $statusLabel,
                        'status_color' => $statusColor,
                        'tanggal_perkawinan' => $p->tanggal_perkawinan?->format('d M Y'),
                        'updated_at' => $p->updated_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal check updates: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'has_updates' => false,
                'updates' => [],
            ], 500);
        }
    }

    /**
     * Get detail dokumen untuk modal
     */
    public function detailDokumen($id): JsonResponse
    {
        try {
            $pernikahan = LayananPernikahan::with(['dokumen' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->find($id);

            if (!$pernikahan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pernikahan tidak ditemukan'
                ], 404);
            }

            $dokumenData = $pernikahan->dokumen->map(function($d) {
                return [
                    'jenis_dokumen_label' => $d->jenis_dokumen_label,
                    'original_filename' => $d->original_filename,
                    'status' => $d->status,
                    'is_image' => $d->isImage(),
                    'is_pdf' => $d->isPdf(),
                    'file_url' => asset('storage/' . $d->file_path),
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'nomor_antrian' => $pernikahan->nomor_antrian,
                'nama_mempelai_pria' => $pernikahan->nama_mempelai_pria,
                'nama_mempelai_wanita' => $pernikahan->nama_mempelai_wanita,
                'tanggal_perkawinan' => $pernikahan->tanggal_perkawinan?->format('d F Y'),
                'dokumen' => $dokumenData,
                'upload_url' => route('keagamaan.pernikahan.upload-berkas'),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail dokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail'
            ], 500);
        }
    }
}
