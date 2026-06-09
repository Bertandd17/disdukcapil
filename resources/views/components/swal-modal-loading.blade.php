{{--
    Swal Modal Loading — Terpusat (Reusable Blade Component)
    =========================================================
    Digunakan oleh seluruh halaman untuk menampilkan loading modal
    tanpa tombol OK/No/Cancel — hanya spinner + pesan.

    Cara pakai:
        @include('components.swal-modal-loading')

    Variabel blade yang tersedia:
        $loadingTitle   = 'Memproses...'
        $loadingMessage  = 'Mohon tunggu sebentar...'
        $loadingIcon     = 'fa-circle-notch fa-spin'
--}}

@php
    $loadingTitle   = $loadingTitle   ?? 'Memproses...';
    $loadingMessage = $loadingMessage ?? 'Mohon tunggu sebentar...';
    $loadingIcon    = $loadingIcon    ?? 'fa-circle-notch fa-spin';
@endphp

{{-- Fungsi JavaScript reusable --}}
@if(!isset($__alreadyDefined))
@php($__alreadyDefined = true)
<script>
/**
 * showLoadingModal() — SweetAlert2 loading modal tanpa tombol
 * @param {string} title   - Judul modal (default: 'Memproses...')
 * @param {string} message - Pesan modal   (default: 'Mohon tunggu sebentar...')
 * @returns {Promise}   SweetAlert2 instance
 */
window.showLoadingModal = function(title, message) {
    return Swal.fire({
        title             : title   || 'Memproses...',
        text              : message || 'Mohon tunggu sebentar...',
        allowOutsideClick : false,
        allowEscapeKey    : false,
        showConfirmButton : false,
        showDenyButton    : false,
        showCancelButton  : false,
        didOpen           : function() { Swal.showLoading(); }
    });
};
</script>
@php($__alreadyDefined = false)
@endif
