{{--
    Komponen Upload Berkas Standar

    Props:
    - $name: Nama input field (required)
    - $label: Label untuk input (required)
    - $required: Apakah wajib diisi (default: false)
    - $accept: Tipe file yang diterima (default: "image/jpeg,image/jpg,image/png,application/pdf")
    - $maxSize: Ukuran maksimal dalam MB (default: 5)
    - $multiple: Apakah bisa upload multiple files (default: false)
    - $helpText: Teks bantuan di bawah input (optional)

    Contoh penggunaan:
    <x-upload-berkas
        name="ktp"
        label="Upload KTP"
        :required="true"
        accept="image/jpeg,image/jpg,image/png"
        :maxSize="2"
        helpText="Format: JPG, JPEG, PNG. Maksimal 2MB"
    />
--}}

<div class="mb-4">
    <label for="{{ $name }}" class="form-label fw-semibold">
        {{ $label }}
        @if($required ?? false)
            <span class="text-danger">*</span>
        @endif
    </label>

    <div class="upload-wrapper">
        <label for="{{ $name }}" class="btn btn-secondary w-100 cursor-pointer d-flex align-items-center justify-content-center gap-2">
            <i class="fas fa-cloud-upload-alt"></i>
            <span class="upload-text">📎 Pilih File</span>
            <input type="file"
                   id="{{ $name }}"
                   name="{{ $name }}{{ ($multiple ?? false) ? '[]' : '' }}"
                   class="d-none upload-input"
                   accept="{{ $accept ?? 'image/jpeg,image/jpg,image/png,application/pdf' }}"
                   {{ ($required ?? false) ? 'required' : '' }}
                   {{ ($multiple ?? false) ? 'multiple' : '' }}
                   data-max-size="{{ ($maxSize ?? 5) * 1024 * 1024 }}">
        </label>

        @if(isset($helpText))
            <small class="form-text text-muted d-block mt-2">
                <i class="fas fa-info-circle"></i> {{ $helpText }}
            </small>
        @endif

        <!-- Preview area -->
        <div id="{{ $name }}-preview" class="mt-3 d-none">
            <div class="alert alert-info d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-file"></i>
                    <span class="file-name"></span>
                    <small class="text-muted file-size"></small>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-file">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('{{ $name }}');
    const preview = document.getElementById('{{ $name }}-preview');
    const uploadText = input.closest('.upload-wrapper').querySelector('.upload-text');
    const maxSize = parseInt(input.dataset.maxSize);

    input.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (!file) {
            preview.classList.add('d-none');
            uploadText.textContent = '📎 Pilih File';
            return;
        }

        // Validasi ukuran file
        if (file.size > maxSize) {
            const maxSizeMB = maxSize / (1024 * 1024);
            SwalHelper.toastError(`Ukuran file terlalu besar. Maksimal ${maxSizeMB}MB`);
            input.value = '';
            return;
        }

        // Tampilkan preview
        preview.classList.remove('d-none');
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = `(${(file.size / 1024).toFixed(2)} KB)`;
        uploadText.textContent = '✓ File Terpilih';

        // Aktifkan tombol submit jika ada
        const submitBtn = input.closest('form')?.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.removeAttribute('disabled');
        }
    });

    // Tombol hapus file
    preview.querySelector('.remove-file')?.addEventListener('click', function() {
        input.value = '';
        preview.classList.add('d-none');
        uploadText.textContent = '📎 Pilih File';

        // Disable tombol submit jika required
        if (input.hasAttribute('required')) {
            const submitBtn = input.closest('form')?.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.setAttribute('disabled', 'disabled');
            }
        }
    });
});
</script>

<style>
.upload-wrapper .cursor-pointer {
    cursor: pointer;
}

.upload-wrapper .btn:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>
