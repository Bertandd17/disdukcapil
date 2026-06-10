<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\OrganisasiModel;
use Illuminate\Http\Request;

class OrganisasiController extends Controller
{
    public function index()
    {
        $roots = OrganisasiModel::roots()->get();
        $allOrganisasi = OrganisasiModel::orderBy('urutan')->get();

        return view('pages.organisasi', [
            'roots' => $roots,
            'allOrganisasi' => $allOrganisasi
        ]);
    }
}
