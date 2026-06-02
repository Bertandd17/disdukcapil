<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_toba.jpeg') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style-guide.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page-loading.css') }}?v={{ filemtime(public_path('css/page-loading.css')) }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #0066FF;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
        }

        .error-wrapper {
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .error-number {
            font-size: clamp(4rem, 15vw, 8rem);
            font-weight: 800;
            color: var(--neutral-white);
            line-height: 1;
            margin-bottom: var(--spacing-md);
            text-align: center;
        }

        .error-title {
            font-size: clamp(1.25rem, 4vw, 1.875rem);
            color: var(--neutral-white);
            font-weight: 700;
            margin-bottom: var(--spacing-xs);
            text-align: center;
        }

        .error-desc {
            font-size: var(--font-size-sm);
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: var(--spacing-lg);
            text-align: center;
        }

        .error-card {
            width: 100%;
            max-height: calc(100vh - 18rem);
            overflow-y: auto;
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl) var(--spacing-lg);
            box-shadow: var(--shadow-xl);
            background: var(--neutral-white);
        }

        .action-list-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            border: 1px solid var(--neutral-100);
            margin-bottom: var(--spacing-sm);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: var(--radius-md);
        }

        .action-text {
            flex: 1;
        }

        .action-buttons {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-lg);
        }

        .action-buttons .btn-action {
            flex: 1;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-primary-solid {
            background: #0066FF;
            color: var(--neutral-white);
        }

        .btn-primary-solid:hover {
            background: #0052CC;
        }

        .btn-secondary-solid {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-secondary-solid:hover {
            background: #d1d5db;
        }

        .error-footer {
            margin-top: var(--spacing-lg);
            font-size: var(--font-size-xs);
            color: rgba(255, 255, 255, 0.65);
            text-align: center;
        }

        @media (max-height: 600px) {
            .error-number {
                font-size: 3rem;
            }
            .error-card {
                padding: var(--spacing-md);
            }
            .action-list-item {
                padding: var(--spacing-sm);
            }
        }

        @media (max-width: 480px) {
            .error-card {
                margin: 0 var(--spacing-sm);
            }
            .action-buttons {
                flex-direction: column;
            }
            .error-number {
                font-size: clamp(3rem, 20vw, 5rem);
            }
        }
    </style>
</head>

<body>
@include('components.page-loading')

    <div class="error-wrapper">
        <div class="action-icon" style="background: rgba(255,255,255,0.15); width: 64px; height: 64px; margin-bottom: var(--spacing-md);">
            <i class="fas fa-map-marker-alt" style="color: var(--neutral-white); font-size: 2rem;"></i>
        </div>

        <h1 class="error-number">404</h1>
        <p class="error-title">Halaman Tidak Ditemukan</p>
        <p class="error-desc">Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>

        <div class="error-card">
            <div style="text-align: center; margin-bottom: var(--spacing-lg);">
                <div class="action-icon" style="background: #DBEAFE; margin: 0 auto var(--spacing-md); width: 56px; height: 56px;">
                    <i class="fas fa-search" style="color: #2563EB; font-size: 1.5rem;"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: var(--spacing-xs);">Halaman Tidak Tersedia</h3>
                <p style="font-size: var(--font-size-sm); color: #4b5563;">Alamat yang Anda akses tidak ditemukan. Coba periksa kembali URL atau kembali ke beranda.</p>
            </div>

            <div class="action-list-item">
                <div class="action-icon" style="background: #FEF3C7;">
                    <i class="fas fa-link" style="color: #D97706;"></i>
                </div>
                <div class="action-text">
                    <p style="font-weight: 600; color: #1f2937; margin: 0;">Periksa URL</p>
                    <p style="font-size: var(--font-size-sm); color: #4b5563; margin: 0;">Pastikan alamat yang Anda masukkan sudah benar dan lengkap.</p>
                </div>
            </div>

            <div class="action-list-item">
                <div class="action-icon" style="background: #F3E8FF;">
                    <i class="fas fa-clock" style="color: #7C3AED;"></i>
                </div>
                <div class="action-text">
                    <p style="font-weight: 600; color: #1f2937; margin: 0;">Halaman Mungkin Dipindahkan</p>
                    <p style="font-size: var(--font-size-sm); color: #4b5563; margin: 0;">Konten mungkin telah dipindahkan atau dihapus oleh administrator.</p>
                </div>
            </div>

            <div class="action-list-item" style="margin-bottom: 0;">
                <div class="action-icon" style="background: #D1FAE5;">
                    <i class="fas fa-home" style="color: #059669;"></i>
                </div>
                <div class="action-text">
                    <p style="font-weight: 600; color: #1f2937; margin: 0;">Kembali ke Beranda</p>
                    <p style="font-size: var(--font-size-sm); color: #4b5563; margin: 0;">Arahkan ke halaman utama untuk menemukan apa yang Anda cari.</p>
                </div>
            </div>

            <div class="action-buttons">
                <a href="{{ route('home') }}" class="btn-action btn-primary-solid">
                    <i class="fas fa-home"></i>
                    Beranda
                </a>
                <button onclick="history.back()" class="btn-action btn-secondary-solid">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </button>
            </div>
        </div>

        <div class="error-footer">
            <p>&copy; {{ date('Y') }} Disdukcapil Kabupaten Toba. Hak Cipta Dilindungi.</p>
            <p style="margin-top: 0.25rem;">Sistem Informasi Kependudukan</p>
        </div>
    </div>

<script src="{{ asset('js/page-loading.js') }}?v={{ filemtime(public_path('js/page-loading.js')) }}"></script>
<script src="{{ asset('js/style-guide-enhancer.js') }}?v={{ filemtime(public_path('js/style-guide-enhancer.js')) }}"></script>
</body>
</html>
