<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Organisasi_Model;
use Illuminate\Http\Request;

class OrganisasiController extends Controller
{
    public function index()
    {
        $roots = Organisasi_Model::roots()->get();
        $allOrganisasi = Organisasi_Model::orderBy('urutan')->get();

        return view('pages.organisasi', [
            'roots' => $roots,
            'allOrganisasi' => $allOrganisasi
        ]);
    }
}
