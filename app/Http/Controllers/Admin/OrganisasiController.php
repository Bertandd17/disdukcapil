<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roots = OrganisasiModel::roots()->get();
        $allOrganisasi = OrganisasiModel::orderBy('urutan')->get();

        return view('admin.organisasi', [
            'roots' => $roots,
            'allOrganisasi' => $allOrganisasi,
            'page_title' => 'Struktur Organisasi'
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organisasi,id',
            'level' => 'required|in:pimpinan_utama,bidang,sub_bagian,koordinator,kelompok_fungsional',
            'nama_pejabat' => 'nullable|string|max:255',
            'eselon' => 'nullable|string|max:50',
            'urutan' => 'required|integer|min:0'
        ]);

        OrganisasiModel::create($validated);

        return redirect()->route('admin.organisasi.index')
            ->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit organisasi');

        $organisasi = OrganisasiModel::findOrFail($id);

        // Validasi hanya untuk nama_pejabat
        $validated = $request->validate([
            'nama_pejabat' => 'nullable|string|max:255',
        ]);

        $organisasi->update($validated);

        return redirect()->route('admin.organisasi.index')
            ->with('success', 'Nama pejabat berhasil diperbarui');
    }

    public function destroy($id)
    {
        $organisasi = OrganisasiModel::findOrFail($id);

        if ($organisasi->children()->count() > 0) {
            return redirect()->route('admin.organisasi.index')
                ->with('error', 'Tidak dapat menghapus jabatan yang memiliki sub-jabatan');
        }

        $organisasi->delete();

        return redirect()->route('admin.organisasi.index')
            ->with('success', 'Jabatan berhasil dihapus');
    }
}
