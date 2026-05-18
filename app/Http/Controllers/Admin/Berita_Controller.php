<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita_Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Berita_Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $beritas = Berita_Model::query()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.kelola_berita', compact('beritas'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'published_at' => 'nullable|date',
        ]);

        $slug = $this->uniqueSlug(Str::slug($validated['judul']));

        $data = [
            'judul' => $validated['judul'],
            'slug' => $slug,
            'konten' => $validated['konten'],
            'published_at' => $validated['published_at'] ?? now(),
        ];

        Berita_Model::create($data);

        return redirect()->route('admin.berita')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function update(Request $request, Berita_Model $berita)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'published_at' => 'nullable|date',
        ]);

        if ($berita->judul !== $validated['judul']) {
            $berita->slug = $this->uniqueSlug(Str::slug($validated['judul']), $berita->id);
        }

        $berita->judul = $validated['judul'];
        $berita->konten = $validated['konten'];
        $berita->published_at = $validated['published_at'] ?? $berita->published_at;

        $berita->save();

        return redirect()->route('admin.berita')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Berita_Model $berita)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $berita->delete();

        return redirect()->route('admin.berita')->with('success', 'Berita berhasil dihapus.');
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $slugBase = $base ?: Str::random(8);
        $n = 1;

        while (
            Berita_Model::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $slugBase . '-' . $n++;
        }

        return $slug;
    }
}
