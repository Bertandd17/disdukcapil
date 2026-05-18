<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penghargaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PenghargaanController extends Controller
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
        $data = Penghargaan::orderBy('created_at', 'desc')->get();
        return view('admin.penghargaan', compact('data'));
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validator = Validator::make($request->all(), [
            'nama'              => 'required|string|max:200',
            'instansi'          => 'required|string|max:200',
            'deskripsi_singkat' => 'required|string',
            'tingkat'           => 'required|in:Nasional,Provinsi,Kabupaten',
            'tahun'             => 'required|digits:4|integer',
            'lokasi'            => 'required|string|max:100',
            'file'              => 'required|file|mimes:pdf|max:512',
        ], [
            'file.max' => 'Ukuran file melebihi batas. Silakan upload file maksimal 512 KB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }

        $validated = $validator->validated();

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('penghargaan', 'public');
        }

        Penghargaan::create($validated);

        return redirect()->route('admin.penghargaan')
            ->with('success', 'Penghargaan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $item = Penghargaan::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'nama'              => 'required|string|max:200',
            'instansi'          => 'required|string|max:200',
            'deskripsi_singkat' => 'required|string',
            'tingkat'           => 'required|in:Nasional,Provinsi,Kabupaten',
            'tahun'             => 'required|digits:4|integer',
            'lokasi'            => 'required|string|max:100',
            'file'              => 'nullable|file|mimes:pdf|max:512',
        ], [
            'file.mimes' => 'File harus berupa PDF.',
            'file.max'   => 'Ukuran file melebihi batas. Silakan upload file maksimal 512 KB.',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }
        $validated = $validator->validated();
        $item->fill($validated);
        if ($request->hasFile('file')) {
            if ($item->file && Storage::disk('public')->exists($item->file)) {
                Storage::disk('public')->delete($item->file);
            }
            $item->file = $request->file('file')->store('penghargaan', 'public');
        }

        $item->save();

        return redirect()->route('admin.penghargaan')
            ->with('success', 'Penghargaan berhasil diupdate.');
    }

    public function destroy($id)
    {
        $this->checkAdmin();
        $item = Penghargaan::findOrFail($id);
        if ($item->file && Storage::disk('public')->exists($item->file)) {
            Storage::disk('public')->delete($item->file);
        }
        $item->delete();
        return redirect()->route('admin.penghargaan')->with('success', 'Penghargaan berhasil dihapus.');
    }
}