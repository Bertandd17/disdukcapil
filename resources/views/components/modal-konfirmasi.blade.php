{{--
    Komponen Modal Konfirmasi Standar

    Props:
    - $id: ID unik modal (required)
    - $title: Judul modal (required)
    - $message: Pesan konfirmasi (required)
    - $confirmText: Teks tombol konfirmasi (default: "Konfirmasi")
    - $confirmColor: Warna tombol konfirmasi - success/danger (default: "success")
    - $formId: ID form yang akan disubmit (optional)
    - $onConfirm: JavaScript function yang akan dipanggil saat konfirmasi (optional)

    Contoh penggunaan:
    <x-modal-konfirmasi
        id="modalHapus"
        title="Hapus Data"
        message="Apakah Anda yakin ingin menghapus data ini?"
        confirmText="Hapus"
        confirmColor="danger"
        formId="formHapus"
    />
--}}

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="font-semibold text-gray-800 text-lg mb-2">{{ $title }}</p>
                <p class="text-gray-500 text-sm">{{ $message }}</p>
            </div>
            <div class="modal-footer d-flex justify-content-between border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    ✕ Batal
                </button>
                <button type="{{ isset($formId) ? 'submit' : 'button' }}"
                        class="btn btn-{{ $confirmColor ?? 'success' }}"
                        @if(isset($formId)) form="{{ $formId }}" @endif
                        @if(isset($onConfirm)) onclick="{{ $onConfirm }}" @endif>
                    ✓ {{ $confirmText ?? 'Konfirmasi' }}
                </button>
            </div>
        </div>
    </div>
</div>
