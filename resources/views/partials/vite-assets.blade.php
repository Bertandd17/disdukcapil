@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        window.tailwind = window.tailwind || { config: {} };
    </script>
@else
    <script src="https://cdn.tailwindcss.com"></script>
@endif
