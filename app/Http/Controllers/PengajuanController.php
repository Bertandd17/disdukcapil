<?php

namespace App\Http\Controllers;

use App\Models\AkteKematian;
use App\Models\LacakBerkasModel;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function status($id)
    {
        $pengajuan = AkteKematian::with(['antrian'])
            ->findOrFail($id);

        $berkas = LacakBerkasModel::where('antrian_online_id', $pengajuan->antrian_online_id ?? null)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('pengajuan.status', compact('pengajuan', 'berkas'));
    }
}