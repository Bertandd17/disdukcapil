<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganisasiController extends Controller
{
public function index() {
    // Mengambil data dari tabel 'organisasi'
    $struktur = \App\Models\Organisasi::orderBy('urutan')->get()->keyBy('kode_posisi');
    return view('admin.organisasi', compact('struktur'));
}

public function update(Request $request, $id) {
    $item = \App\Models\Organisasi::findOrFail($id);
    $item->update($request->only('nama_pejabat', 'nama_jabatan'));
    
    return redirect()->back()->with('success', 'Struktur Organisasi Berhasil Diperbarui!');
}
}
