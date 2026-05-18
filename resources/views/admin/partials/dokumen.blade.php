<div class="border rounded-lg p-4 text-center hover:shadow">
    <p class="text-sm font-semibold mb-3">{{ $label }}</p>

    <a href="{{ route('admin.lihat-berkas', [
        'uuid' => $berkas->uuid,
        'jenis' => $jenis,
        'field' => $field
    ]) }}"
    target="_blank"
    class="block w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white py-2 rounded-lg text-sm transition">
        Lihat Berkas
    </a>
</div>