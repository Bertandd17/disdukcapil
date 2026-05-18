<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DasarHukum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DasarHukumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function checkAdmin()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        $data = DasarHukum::orderBy('created_at', 'desc')->get();
        return view('admin.dasar_hukum', compact('data'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'nama'              => 'required|string|max:100',
            'deskripsi_singkat' => 'required|string',
            'file'              => 'required|file|mimes:pdf|max:512',
        ]);
        $validated['file'] = $request->file('file')->store('dasar-hukum', 'public');
        DasarHukum::create($validated);
        return redirect()->route('admin.dasar-hukum')->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $item = DasarHukum::findOrFail($id);
        $validated = $request->validate([
            'nama'              => 'required|string|max:100',
            'deskripsi_singkat' => 'required|string',
            'file'              => 'nullable|file|mimes:pdf|max:512',
        ]);
        $item->nama              = $validated['nama'];
        $item->deskripsi_singkat = $validated['deskripsi_singkat'];
        if ($request->hasFile('file')) {
            if ($item->file && Storage::disk('public')->exists($item->file)) {
                Storage::disk('public')->delete($item->file);
            }
            $item->file = $request->file('file')->store('dasar-hukum', 'public');
        }
        $item->save();
        return redirect()->route('admin.dasar-hukum')->with('success', 'Data berhasil diupdate.');
    }

    public function destroy($id)
    {
        $this->checkAdmin();
        $item = DasarHukum::findOrFail($id);
        if ($item->file && Storage::disk('public')->exists($item->file)) {
            Storage::disk('public')->delete($item->file);
        }
        $item->delete();
        return redirect()->route('admin.dasar-hukum')->with('success', 'Data berhasil dihapus.');
    }
}