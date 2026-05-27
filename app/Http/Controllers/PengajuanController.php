<?php

namespace App\Http\Controllers;

use App\Models\AkteKematian;
use App\Models\Lacak_Berkas_Model;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function status($id)
    {
        $pengajuan = AkteKematian::with(['antrian'])
            ->findOrFail($id);

        $berkas = Lacak_Berkas_Model::where('antrian_online_id', $pengajuan->antrian_online_id ?? null)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('pengajuan.status', compact('pengajuan', 'berkas'));
    }
}