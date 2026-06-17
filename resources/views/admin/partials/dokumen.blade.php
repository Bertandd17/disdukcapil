<div class="border rounded-xl p-4 flex flex-col justify-between hover:shadow-md transition bg-gray-50">
    <div class="mb-4">
        <p class="text-sm font-bold text-gray-800">{{ $label }}</p>
        @if($berkas->{$field})
            <p class="text-xs text-green-600 mt-1"><i class="fas fa-check-circle mr-1"></i> Terunggah</p>
        @else
            <p class="text-xs text-red-500 mt-1"><i class="fas fa-times-circle mr-1"></i> Tidak Ada</p>
        @endif
    </div>

    @if($berkas->{$field})
        <a href="{{ route('admin.lihat-berkas', [
            'uuid' => $berkas->uuid,
            'jenis' => $jenis,
            'field' => $field
        ]) }}"
        target="_blank"
        data-style-guide-skip
        class="w-full bg-green-600 text-white py-2 rounded-lg text-sm font-semibold text-center flex items-center justify-center">
            <i class="fas fa-external-link-alt mr-2"></i> Buka Dokumen
        </a>
    @else
        <button disabled class="w-full bg-gray-200 text-gray-400 py-2 rounded-lg text-sm font-semibold cursor-not-allowed">
            Berkas Kosong
        </button>
    @endif
</div>
