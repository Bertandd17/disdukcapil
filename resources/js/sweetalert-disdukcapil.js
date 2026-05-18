/**
 * =====================================================
 * SWEETALERT2 NOTIFICATION SYSTEM - DISDUKCAPIL TOBA
 * =====================================================
 * Sistem notifikasi SweetAlert2 yang dikustomisasi untuk
 * Sistem Informasi Dinas Kependudukan dan Pencatatan Sipil
 * 
 * @author Disdukcapil Toba
 * @version 1.0.0
 * @license MIT
 * @requires SweetAlert2 v11.x
 * =====================================================
 */

(function(window) {
    'use strict';

    /**
     * =====================================================
     * KONFIGURASI GLOBAL
     * =====================================================
     */
    const SwalDisdukcapil = {
        // Tema warna gradient
        themes: {
            success: {
                background: 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
                color: '#ffffff'
            },
            error: {
                background: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                color: '#ffffff'
            },
            warning: {
                background: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                color: '#ffffff'
            },
            info: {
                background: 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
                color: '#ffffff'
            },
            question: {
                background: 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
                color: '#ffffff'
            },
            modal: {
                background: '#ffffff',
                color: '#1f2937'
            }
        },

        // Konfigurasi default toast
        toastDefaults: {
            position: 'top-end',
            timerProgressBar: true,
            showConfirmButton: false,
            timer: 4000,
            toast: true,
            allowOutsideClick: false,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        },

        // Konfigurasi default modal
        modalDefaults: {
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            showConfirmButton: true,
            reverseButtons: true
        }
    };

    /**
     * =====================================================
     * KUMPULAN FUNGSI NOTIFIKASI
     * =====================================================
     */

    /**
     * =====================================================
     * 1. SIMPAN / UPDATE DATA
     * =====================================================
     */

    /**
     * Notifikasi sukses setelah data berhasil disimpan
     * 
     * @param {string} nomorRegistrasi - Nomor registrasi (contoh: #REG-2024-001234)
     * 
     * @example
     * // PHP: echo "<script>notifSimpanBerhasil('{$data->no_registrasi}')</script>";
     * notifSimpanBerhasil('#REG-2024-001234');
     */
    function notifSimpanBerhasil(nomorRegistrasi) {
        const theme = SwalDisdukcapil.themes.success;
        
        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'success',
            title: '<span style="color: #16a34a; font-weight: 700;">Berhasil!</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
                    <p style="margin: 0 0 8px 0; color: #374151; font-weight: 600;">Data berhasil disimpan</p>
                    <p style="margin: 0; font-size: 18px; font-weight: 700; color: #16a34a; font-family: monospace;">
                        ${nomorRegistrasi}
                    </p>
                </div>
            `,
            timer: 4000,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                title: 'text-lg font-bold',
                htmlContainer: 'text-sm'
            }
        });
    }

    /**
     * Notifikasi gagal saat penyimpanan data
     * 
     * @param {string} pesanError - Pesan error dari server
     * 
     * @example
     * // PHP: echo "<script>notifSimpanGagal('{$errorMessage}')</script>";
     * notifSimpanGagal('NIK sudah terdaftar dalam sistem');
     */
    function notifSimpanGagal(pesanError) {
        const theme = SwalDisdukcapil.themes.error;
        
        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'error',
            title: '<span style="color: #dc2626; font-weight: 700;">Gagal!</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
                    <p style="margin: 0 0 8px 0; color: #374151; font-weight: 600;">Data gagal disimpan</p>
                    <p style="margin: 0; color: #dc2626; font-size: 14px;">${pesanError}</p>
                </div>
            `,
            timer: 4000,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl border-l-4 border-red-500',
                title: 'text-lg font-bold'
            }
        });
    }

    /**
     * =====================================================
     * 2. VALIDASI FORM
     * =====================================================
     */

    /**
     * Notifikasi validasi gagal dengan daftar kesalahan
     * 
     * @param {string[]} arrayKesalahan - Array pesan kesalahan validasi
     * 
     * @example
     * const errors = [
     *     "NIK wajib diisi (16 digit)",
     *     "Format tanggal tidak valid",
     *     "KTP scan wajib diunggah"
     * ];
     * notifValidasiGagal(errors);
     */
    function notifValidasiGagal(arrayKesalahan) {
        const theme = SwalDisdukcapil.themes.error;
        const listItems = arrayKesalahan.map(err => `
            <li style="padding: 8px 12px; margin: 4px 0; background: #fef2f2; border-left: 3px solid #dc2626; border-radius: 6px; color: #991b1b; font-size: 13px;">
                <i class="fas fa-exclamation-circle" style="color: #dc2626; margin-right: 8px;"></i>
                ${err}
            </li>
        `).join('');

        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'error',
            title: '<span style="color: #dc2626; font-weight: 700;">Validasi Gagal!</span>',
            html: `
                <div style="text-align: left; padding: 10px 0;">
                    <p style="margin: 0 0 12px 0; color: #374151; font-weight: 600;">
                        <i class="fas fa-list-ul" style="color: #dc2626; margin-right: 8px;"></i>
                        ${arrayKesalahan.length} kesalahan ditemukan:
                    </p>
                    <ul style="margin: 0; padding: 0; list-style: none; max-height: 200px; overflow-y: auto;">
                        ${listItems}
                    </ul>
                </div>
            `,
            timer: 5000,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                title: 'text-lg font-bold'
            }
        });
    }

    /**
     * Notifikasi form belum lengkap dengan tombol aksi
     * 
     * @example
     * notifFormBelumLengkap();
     */
    function notifFormBelumLengkap() {
        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'warning',
            title: '<span style="color: #d97706; font-weight: 700;">Form Belum Lengkap!</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 48px; margin-bottom: 10px;">⚠️</div>
                    <p style="margin: 0 0 16px 0; color: #374151;">
                        Pastikan semua field wajib terisi dengan benar.
                    </p>
                    <button onclick="Swal.close()" class="swal2-confirm swal2-styled" style="
                        background: linear-gradient(135deg, #f59e0b, #d97706);
                        border: none;
                        padding: 10px 24px;
                        border-radius: 8px;
                        color: white;
                        font-weight: 600;
                        cursor: pointer;
                        transition: transform 0.2s;
                    " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fas fa-edit mr-2"></i> Lengkapi Data
                    </button>
                </div>
            `,
            showConfirmButton: false,
            timer: 6000,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl'
            }
        });
    }

    /**
     * =====================================================
     * 3. UPLOAD DOKUMEN
     * =====================================================
     */

    /**
     * Notifikasi proses upload dengan progress dan handle success/error
     * 
     * @param {string} namaFile - Nama file yang diupload
     * @param {Function} onSuccess - Callback saat upload berhasil (Promise resolve)
     * @param {Function} onError - Callback saat upload gagal (Promise reject)
     * 
     * @example
     * const uploadPromise = () => {
     *     const formData = new FormData();
     *     formData.append('file', fileInput.files[0]);
     *     return fetch('upload.php', {
     *         method: 'POST',
     *         body: formData
     *     }).then(r => r.json());
     * };
     * 
     * notifUploadProses(
     *     'KTP_Scan.pdf',
     *     () => { /* refresh table *\/ },
     *     () => { /* show retry *\/ }
     * );
     */
    async function notifUploadProses(namaFile, onSuccess, onError) {
        // Tampilkan loading modal
        Swal.fire({
            title: '<span style="font-weight: 700;">Mengunggah File...</span>',
            html: `
                <div style="text-align: center; padding: 20px 0;">
                    <div class="swal2-loader" style="margin-bottom: 20px;"></div>
                    <p style="margin: 0; color: #6b7280; font-size: 14px;">
                        <i class="fas fa-file-upload" style="margin-right: 8px;"></i>
                        ${namaFile}
                    </p>
                    <p style="margin: 12px 0 0 0; color: #9ca3af; font-size: 12px;">
                        Mohon tunggu, sedang mengunggah...
                    </p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            background: '#ffffff',
            didOpen: async () => {
                try {
                    // Jalankan callback upload
                    const result = await onSuccess();
                    
                    // Loading element
                    const loader = Swal.getContainer().querySelector('.swal2-loader');
                    
                    // Tutup loading dan tampilkan toast success
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 20px;">✅</span>
                                <span style="font-weight: 600;">File berhasil diunggah</span>
                            </div>
                        `,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        background: '#ffffff',
                        customClass: {
                            popup: 'rounded-xl shadow-lg'
                        }
                    });
                    
                    return result;
                    
                } catch (error) {
                    // Tampilkan modal error expanded
                    Swal.fire({
                        icon: 'error',
                        title: '<span style="color: #dc2626; font-weight: 700;">Upload Gagal!</span>',
                        html: `
                            <div style="text-align: center; padding: 20px 0;">
                                <div style="font-size: 64px; margin-bottom: 16px;">❌</div>
                                <p style="margin: 0 0 8px 0; color: #374151; font-weight: 600; font-size: 16px;">
                                    File gagal diunggah
                                </p>
                                <p style="margin: 0 0 16px 0; color: #6b7280; font-size: 14px;">
                                    ${error.message || 'Terjadi kesalahan saat mengunggah file'}
                                </p>
                                <div style="background: #f3f4f6; border-radius: 8px; padding: 12px; text-align: left; margin-bottom: 20px;">
                                    <p style="margin: 0 0 8px 0; color: #374151; font-weight: 600; font-size: 13px;">
                                        <i class="fas fa-lightbulb" style="color: #f59e0b; margin-right: 6px;"></i>
                                        Saran:
                                    </p>
                                    <ul style="margin: 0; padding-left: 20px; color: #6b7280; font-size: 12px;">
                                        <li>Format file: PDF, JPG, atau PNG</li>
                                        <li>Maksimal ukuran: 5MB</li>
                                        <li>Pastikan koneksi internet stabil</li>
                                    </ul>
                                </div>
                            </div>
                        `,
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: '<i class="fas fa-redo mr-2"></i> Coba Lagi',
                        confirmButtonColor: '#16a34a',
                        confirmButtonClass: 'swal2-confirm swal2-styled',
                        background: '#ffffff',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl'
                        }
                    }).then((result) => {
                        if (result.isConfirmed && onError) {
                            onError();
                        }
                    });
                }
            }
        });
    }

    /**
     * =====================================================
     * 4. PROSES PENGAJUAN
     * =====================================================
     */

    /**
     * Notifikasi proses pengajuan dengan modal loading dan hasil
     * 
     * @param {string} jenisDokumen - Jenis dokumen (contoh: "Kartu Keluarga", "Akta Kelahiran")
     * @param {Function} onSelesai - Callback saat tombol "Selesai" diklik
     * @param {Function} onTambah - Callback saat tombol "Tambah Pengajuan Lagi" diklik
     * 
     * @example
     * const prosesPengajuan = async () => {
     *     const response = await fetch('pengajuan.php', {
     *         method: 'POST',
     *         body: JSON.stringify(formData)
     *     });
     *     return response.json();
     * };
     * 
     * notifPengajuanProses(
     *     'Kartu Keluarga',
     *     () => window.location.href = '/dashboard',
     *     () => resetForm()
     * );
     */
    async function notifPengajuanProses(jenisDokumen, onSelesai, onTambah) {
        // Tampilkan loading
        const loadingResult = await Swal.fire({
            title: '<span style="font-weight: 700;">Memproses Pengajuan...</span>',
            html: `
                <div style="text-align: center; padding: 20px 0;">
                    <div class="swal2-loading" style="margin-bottom: 20px;">
                        <div class="spinner-border text-success" role="status"></div>
                    </div>
                    <p style="margin: 0; color: #374151; font-weight: 600; font-size: 16px;">
                        <i class="fas fa-file-alt" style="margin-right: 8px; color: #16a34a;"></i>
                        ${jenisDokumen}
                    </p>
                    <p style="margin: 12px 0 0 0; color: #9ca3af; font-size: 14px;">
                        Mohon tunggu, data sedang diproses...
                    </p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            background: '#ffffff',
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Jika cancelled
        if (loadingResult.isDismissed) return;

        // Generate nomor registrasi otomatis
        const now = new Date();
        const year = now.getFullYear();
        const randomNum = Math.floor(Math.random() * 999999).toString().padStart(6, '0');
        const nomorRegistrasi = `#REG-${year}-${randomNum}`;

        // Tampilkan modal sukses expanded
        Swal.fire({
            icon: 'success',
            title: '<span style="color: #16a34a; font-weight: 700;">Pengajuan Berhasil!</span>',
            html: `
                <div style="text-align: center; padding: 20px 0;">
                    <div style="font-size: 80px; margin-bottom: 16px;">🎉</div>
                    <p style="margin: 0 0 8px 0; color: #374151; font-weight: 600; font-size: 16px;">
                        Pengajuan ${jenisDokumen} berhasil diajukan
                    </p>
                    <div style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); border-radius: 12px; padding: 16px; margin: 16px 0;">
                        <p style="margin: 0 0 4px 0; color: #166534; font-size: 12px; font-weight: 600;">
                            NOMOR REGISTRASI
                        </p>
                        <p style="margin: 0; color: #16a34a; font-size: 24px; font-weight: 700; font-family: monospace;">
                            ${nomorRegistrasi}
                        </p>
                    </div>
                    <p style="margin: 0 0 16px 0; color: #6b7280; font-size: 13px;">
                        <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
                        Simpan nomor registrasi untuk melacak status pengajuan
                    </p>
                </div>
            `,
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-check mr-2"></i> Selesai',
            confirmButtonColor: '#16a34a',
            confirmButtonClass: 'swal2-confirm swal2-styled',
            cancelButtonText: '<i class="fas fa-plus mr-2"></i> Tambah Pengajuan Lagi',
            cancelButtonColor: '#6b7280',
            cancelButtonClass: 'swal2-cancel swal2-styled',
            reverseButtons: true,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3',
                cancelButton: 'rounded-lg px-6 py-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (onSelesai) onSelesai();
            } else if (result.isDismissed) {
                if (onTambah) onTambah();
            }
        });
    }

    /**
     * Notifikasi pengajuan ditolak dengan alasan
     * 
     * @param {string} alasan - Alasan penolakan dari admin
     * 
     * @example
     * notifPengajuanDitolak('Data yang diisi tidak sesuai dengan dokumen asli. Mohon perbaiki NIK dan alamat.');
     */
    function notifPengajuanDitolak(alasan) {
        Swal.fire({
            icon: 'warning',
            title: '<span style="color: #d97706; font-weight: 700;">Pengajuan Ditolak!</span>',
            html: `
                <div style="text-align: center; padding: 20px 0;">
                    <div style="font-size: 64px; margin-bottom: 16px;">📋</div>
                    <p style="margin: 0 0 16px 0; color: #374151; font-weight: 600; font-size: 16px;">
                        Pengajuan tidak dapat diproses
                    </p>
                    <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; padding: 16px; text-align: left; margin-bottom: 20px;">
                        <p style="margin: 0 0 8px 0; color: #9a3412; font-weight: 600; font-size: 13px;">
                            <i class="fas fa-exclamation-triangle" style="color: #d97706; margin-right: 6px;"></i>
                            Alasan Penolakan:
                        </p>
                        <p style="margin: 0; color: #451a03; font-size: 14px; line-height: 1.6;">
                            ${alasan}
                        </p>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button onclick="Swal.close()" class="swal2-confirm swal2-styled" style="
                            flex: 1;
                            background: linear-gradient(135deg, #f59e0b, #d97706);
                            border: none;
                            padding: 12px 16px;
                            border-radius: 8px;
                            color: white;
                            font-weight: 600;
                            cursor: pointer;
                        ">
                            <i class="fas fa-edit mr-2"></i> Edit Data
                        </button>
                        <button onclick="Swal.close()" class="swal2-cancel swal2-styled" style="
                            flex: 1;
                            background: #6b7280;
                            border: none;
                            padding: 12px 16px;
                            border-radius: 8px;
                            color: white;
                            font-weight: 600;
                            cursor: pointer;
                        ">
                            <i class="fas fa-headset mr-2"></i> Hubungi Admin
                        </button>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl'
            }
        });
    }

    /**
     * =====================================================
     * 5. HAPUS DATA
     * =====================================================
     */

    /**
     * Notifikasi konfirmasi hapus data
     * 
     * @param {string} namaPenduduk - Nama penduduk yang akan dihapus
     * @param {Function} onHapus - Callback saat konfirmasi hapus
     * 
     * @example
     * <button onclick="notifKonfirmasiHapus('{{ $penduduk->nama }}', hapusData)">
     * 
     * function hapusData() {
     *     fetch('hapus.php?id=123', { method: 'DELETE' })
     *         .then(r => r.json())
     *         .then(data => {
     *             if (data.status === 'success') {
     *                 location.reload();
     *             }
     *         });
     * }
     */
    function notifKonfirmasiHapus(namaPenduduk, onHapus) {
        Swal.fire({
            icon: 'warning',
            title: '<span style="color: #dc2626; font-weight: 700;">Konfirmasi Hapus!</span>',
            html: `
                <div style="text-align: center; padding: 20px 0;">
                    <div style="font-size: 64px; margin-bottom: 16px;">⚠️</div>
                    <p style="margin: 0 0 8px 0; color: #374151; font-weight: 600; font-size: 16px;">
                        Anda akan menghapus data:
                    </p>
                    <div style="background: #fef2f2; border: 2px solid #fca5a5; border-radius: 12px; padding: 16px; margin: 16px 0;">
                        <p style="margin: 0; color: #991b1b; font-size: 18px; font-weight: 700;">
                            <i class="fas fa-user mr-2"></i>${namaPenduduk}
                        </p>
                    </div>
                    <p style="margin: 0; color: #dc2626; font-size: 14px; font-weight: 500;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
                        Tindakan ini tidak dapat dibatalkan!
                    </p>
                </div>
            `,
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-trash-alt mr-2"></i> Ya, Hapus',
            confirmButtonColor: '#dc2626',
            confirmButtonClass: 'swal2-confirm swal2-styled',
            cancelButtonText: '<i class="fas fa-times mr-2"></i> Batal',
            cancelButtonColor: '#6b7280',
            cancelButtonClass: 'swal2-cancel swal2-styled',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3',
                cancelButton: 'rounded-lg px-6 py-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (onHapus) onHapus();
            }
        });
    }

    /**
     * =====================================================
     * 6. ANTRIAN
     * =====================================================
     */

    /**
     * Notifikasi nomor antrian dengan detail
     * 
     * @param {string} nomorAntrian - Nomor antrian (contoh: "A-001")
     * @param {string} jenisLayanan - Jenis layanan (contoh: "Kartu Tanda Penduduk")
     * @param {string} estimasiWaktu - Estimasi waktu tunggu (contoh: "15 menit")
     * 
     * @example
     * notifNomorAntrian('A-001', 'Kartu Tanda Penduduk (KTP)', '15 menit');
     */
    function notifNomorAntrian(nomorAntrian, jenisLayanan, estimasiWaktu) {
        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'info',
            title: '<span style="color: #2563eb; font-weight: 700;">Nomor Antrian Anda</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 16px; padding: 24px 32px; margin-bottom: 16px;">
                        <p style="margin: 0 0 4px 0; color: rgba(255,255,255,0.8); font-size: 12px; font-weight: 600;">
                            NOMOR ANTRIAN
                        </p>
                        <p style="margin: 0; color: white; font-size: 48px; font-weight: 800; font-family: monospace; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">
                            ${nomorAntrian}
                        </p>
                    </div>
                    <div style="display: flex; justify-content: center; gap: 20px;">
                        <div style="text-align: center;">
                            <p style="margin: 0 0 4px 0; color: #9ca3af; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-concierge-bell" style="margin-right: 4px;"></i> LAYANAN
                            </p>
                            <p style="margin: 0; color: #2563eb; font-size: 13px; font-weight: 700;">
                                ${jenisLayanan}
                            </p>
                        </div>
                        <div style="text-align: center;">
                            <p style="margin: 0 0 4px 0; color: #9ca3af; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-clock" style="margin-right: 4px;"></i> ESTIMASI
                            </p>
                            <p style="margin: 0; color: #d97706; font-size: 13px; font-weight: 700;">
                                ${estimasiWaktu}
                            </p>
                        </div>
                    </div>
                </div>
            `,
            timer: 8000,
            showConfirmButton: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                title: 'text-lg font-bold'
            }
        });
    }

    /**
     * Notifikasi pencarian berhasil
     * 
     * @param {number} jumlahHasil - Jumlah data ditemukan
     * @param {string} keyword - Keyword pencarian
     * 
     * @example
     * notifCariBerhasil(5, 'Sitorus');
     */
    function notifCariBerhasil(jumlahHasil, keyword) {
        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'success',
            title: '<span style="color: #16a34a; font-weight: 700;">Ditemukan!</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
                    <p style="margin: 0; color: #374151; font-weight: 600;">
                        ${jumlahHasil} data ditemukan untuk
                    </p>
                    <p style="margin: 8px 0 0 0; color: #16a34a; font-size: 16px; font-weight: 700;">
                        "${keyword}"
                    </p>
                </div>
            `,
            timer: 3000,
            showConfirmButton: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-xl shadow-lg',
                title: 'text-base font-bold'
            }
        });
    }

    /**
     * Notifikasi pencarian kosong
     * 
     * @param {string} keyword - Keyword pencarian
     * 
     * @example
     * notifCariKosong('Sitoruss');
     */
    function notifCariKosong(keyword) {
        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'warning',
            title: '<span style="color: #d97706; font-weight: 700;">Tidak Ditemukan</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                    <p style="margin: 0; color: #374151; font-weight: 600;">
                        Tidak ada data untuk
                    </p>
                    <p style="margin: 8px 0 0 0; color: #d97706; font-size: 16px; font-weight: 700;">
                        "${keyword}"
                    </p>
                    <p style="margin: 12px 0 0 0; color: #9ca3af; font-size: 13px;">
                        <i class="fas fa-info-circle" style="margin-right: 4px;"></i>
                        Periksa ejaan atau gunakan kata kunci lain
                    </p>
                </div>
            `,
            timer: 3500,
            showConfirmButton: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-xl shadow-lg',
                title: 'text-base font-bold'
            }
        });
    }

    /**
     * =====================================================
     * 7. KONFIRMASI AKSI UMUM
     * =====================================================
     */

    /**
     * Notifikasi konfirmasi aksi umum
     * 
     * @param {string} pesan - Pesan konfirmasi
     * @param {Function} onSetuju - Callback saat "Ya, Lanjutkan" diklik
     * @param {Function|null} onBatal - Callback saat "Batal" diklik (opsional)
     * 
     * @example
     * notifKonfirmasi(
     *     'Apakah Anda yakin ingin memverifikasi data ini?',
     *     () => { /* proses verifikasi *\/ },
     *     () => { /* handle cancel *\/ }
     * );
     */
    function notifKonfirmasi(pesan, onSetuju, onBatal = null) {
        Swal.fire({
            icon: 'question',
            title: '<span style="font-weight: 700;">Konfirmasi</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 48px; margin-bottom: 16px;">❓</div>
                    <p style="margin: 0; color: #374151; font-size: 15px; line-height: 1.6;">
                        ${pesan}
                    </p>
                </div>
            `,
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-check mr-2"></i> Ya, Lanjutkan',
            confirmButtonColor: '#16a34a',
            confirmButtonClass: 'swal2-confirm swal2-styled',
            cancelButtonText: '<i class="fas fa-times mr-2"></i> Batal',
            cancelButtonColor: '#6b7280',
            cancelButtonClass: 'swal2-cancel swal2-styled',
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-lg px-6 py-3',
                cancelButton: 'rounded-lg px-6 py-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (onSetuju) onSetuju();
            } else if (result.isDismissed) {
                if (onBatal) onBatal();
            }
        });
    }

    /**
     * Notifikasi sukses setelah aksi disetujui
     * 
     * @param {string} namaPemohon - Nama pemohon
     * 
     * @example
     * notifDisetujui('Budi Santoso');
     */
    function notifDisetujui(namaPemohon) {
        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: 'success',
            title: '<span style="color: #16a34a; font-weight: 700;">Disetujui!</span>',
            html: `
                <div style="text-align: center; padding: 10px 0;">
                    <div style="background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 50px; padding: 4px 12px; display: inline-block; margin-bottom: 12px;">
                        <span style="color: white; font-size: 10px; font-weight: 700; letter-spacing: 1px;">APPROVED</span>
                    </div>
                    <p style="margin: 0; color: #374151; font-weight: 600;">
                        Pengajuan
                    </p>
                    <p style="margin: 4px 0 0 0; color: #16a34a; font-size: 18px; font-weight: 700;">
                        ${namaPemohon}
                    </p>
                    <p style="margin: 8px 0 0 0; color: #374151;">
                        telah disetujui
                    </p>
                </div>
            `,
            timer: 4000,
            showConfirmButton: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                title: 'text-lg font-bold'
            }
        });
    }

    /**
     * =====================================================
     * FUNGSI UTILITAS TAMBAHAN
     * =====================================================
     */

    /**
     * Notifikasi loading umum
     * 
     * @param {string} pesan - Pesan loading
     * @returns {Promise} - Resolve saat Swal.close() dipanggil
     * 
     * @example
     * await notifLoading('Memproses data...');
     * // Do something
     * Swal.close();
     */
    function notifLoading(pesan = 'Memproses...') {
        return Swal.fire({
            title: '<span style="font-weight: 700;">' + pesan + '</span>',
            html: `
                <div style="text-align: center; padding: 20px 0;">
                    <div class="swal2-loading" style="margin-bottom: 16px;">
                        <div style="width: 40px; height: 40px; border: 4px solid #e5e7eb; border-top: 4px solid #16a34a; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    </div>
                    <p style="margin: 0; color: #6b7280; font-size: 14px;">Mohon tunggu...</p>
                </div>
                <style>
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            background: '#ffffff'
        }).fire();
    }

    /**
     * Notifikasi toast custom
     * 
     * @param {string} icon - Icon: success, error, warning, info
     * @param {string} judul - Judul notifikasi
     * @param {string} pesan - Isi pesan
     * @param {number} durasi - Durasi dalam ms
     * 
     * @example
     * notifToast('success', 'Berhasil!', 'Data berhasil disimpan');
     */
    function notifToast(icon, judul, pesan, durasi = 4000) {
        const colors = {
            success: '#16a34a',
            error: '#dc2626',
            warning: '#d97706',
            info: '#2563eb'
        };

        const emojis = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        Swal.fire({
            ...SwalDisdukcapil.toastDefaults,
            icon: icon,
            title: `<span style="color: ${colors[icon]}; font-weight: 700;">${judul}</span>`,
            html: `
                <div style="text-align: center;">
                    <div style="font-size: 24px; margin-bottom: 8px;">${emojis[icon]}</div>
                    <p style="margin: 0; color: #374151;">${pesan}</p>
                </div>
            `,
            timer: durasi,
            showConfirmButton: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-xl shadow-lg'
            }
        });
    }

    // Export semua fungsi ke window object
    window.SwalDisdukcapil = {
        themes: SwalDisdukcapil.themes,
        notifSimpanBerhasil,
        notifSimpanGagal,
        notifValidasiGagal,
        notifFormBelumLengkap,
        notifUploadProses,
        notifPengajuanProses,
        notifPengajuanDitolak,
        notifKonfirmasiHapus,
        notifNomorAntrian,
        notifCariBerhasil,
        notifCariKosong,
        notifKonfirmasi,
        notifDisetujui,
        notifLoading,
        notifToast
    };

    // Alias untuk backward compatibility
    window.notifSimpanBerhasil = notifSimpanBerhasil;
    window.notifSimpanGagal = notifSimpanGagal;
    window.notifValidasiGagal = notifValidasiGagal;
    window.notifFormBelumLengkap = notifFormBelumLengkap;
    window.notifUploadProses = notifUploadProses;
    window.notifPengajuanProses = notifPengajuanProses;
    window.notifPengajuanDitolak = notifPengajuanDitolak;
    window.notifKonfirmasiHapus = notifKonfirmasiHapus;
    window.notifNomorAntrian = notifNomorAntrian;
    window.notifCariBerhasil = notifCariBerhasil;
    window.notifCariKosong = notifCariKosong;
    window.notifKonfirmasi = notifKonfirmasi;
    window.notifDisetujui = notifDisetujui;
    window.notifLoading = notifLoading;
    window.notifToast = notifToast;

})(window);
