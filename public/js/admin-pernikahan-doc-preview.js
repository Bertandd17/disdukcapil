(function (window) {
    'use strict';

    function escapeHtml(value) {
        if (value == null) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeFileEntry(entry) {
        if (!entry) return null;

        if (typeof entry === 'string') {
            var url = entry;
            var isPdf = /\.pdf(\?|$)/i.test(url);
            var isImage = !isPdf && /\.(jpe?g|png|webp|gif)(\?|$)/i.test(url);
            return { url: url, is_pdf: isPdf, is_image: isImage };
        }

        var url = entry.url || entry.file_url || null;
        if (!url) return null;

        var isPdf = entry.is_pdf;
        var isImage = entry.is_image;
        if (isPdf === undefined && isImage === undefined) {
            isPdf = /\.pdf(\?|$)/i.test(url);
            isImage = !isPdf && /\.(jpe?g|png|webp|gif)(\?|$)/i.test(url);
        }

        return {
            url: url,
            is_pdf: !!isPdf,
            is_image: !!isImage,
            label: entry.label || entry.jenis_dokumen_label || null
        };
    }

    function renderDocPreview(entry, label) {
        var file = normalizeFileEntry(entry);
        if (!file || !file.url) return '';

        var title = label || file.label || 'Dokumen';
        var safeUrl = escapeHtml(file.url);
        var safeTitle = escapeHtml(title);
        var body = '';

        if (file.is_pdf) {
            body =
                '<iframe src="' + safeUrl + '" class="w-full h-72 rounded-lg border border-gray-200 bg-white" title="' + safeTitle + '"></iframe>' +
                '<a href="' + safeUrl + '" target="_blank" rel="noopener" class="inline-flex items-center text-xs text-blue-600 hover:underline mt-2">' +
                '<i class="fas fa-external-link-alt mr-1"></i>Buka PDF</a>';
        } else if (file.is_image) {
            body =
                '<img src="' + safeUrl + '" alt="' + safeTitle + '" ' +
                'class="w-full max-h-72 object-contain rounded-lg border border-gray-200 cursor-pointer hover:opacity-90" ' +
                'onclick="window.open(\'' + safeUrl + '\',\'_blank\')">';
        } else {
            body =
                '<a href="' + safeUrl + '" target="_blank" rel="noopener" class="inline-flex items-center text-sm text-blue-600 hover:underline">' +
                '<i class="fas fa-eye mr-1"></i>Lihat ' + safeTitle + '</a>';
        }

        return (
            '<div class="border rounded-lg p-3 bg-gray-50">' +
            '<p class="text-xs font-medium text-gray-700 mb-2">' + safeTitle + '</p>' +
            body +
            '</div>'
        );
    }

    function renderKtpFilesGrid(ktpFiles) {
        if (!ktpFiles || typeof ktpFiles !== 'object') {
            return '';
        }

        var entries = [
            ['mempelai_pria', 'KTP Mempelai Pria'],
            ['mempelai_wanita', 'KTP Mempelai Wanita'],
            ['saksi_1', 'KTP Saksi 1'],
            ['saksi_2', 'KTP Saksi 2']
        ];

        var html = entries
            .map(function (pair) {
                return renderDocPreview(ktpFiles[pair[0]], pair[1]);
            })
            .filter(Boolean)
            .join('');

        if (!html) {
            return '<p class="text-sm text-gray-500 italic">Belum ada berkas KTP diunggah.</p>';
        }

        return (
            '<div class="border-t pt-4 mt-4">' +
            '<h4 class="font-semibold text-gray-800 mb-3">Berkas KTP</h4>' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' + html + '</div>' +
            '</div>'
        );
    }

    window.AdminPernikahanDocPreview = {
        normalizeFileEntry: normalizeFileEntry,
        renderDocPreview: renderDocPreview,
        renderKtpFilesGrid: renderKtpFilesGrid
    };
})(window);
