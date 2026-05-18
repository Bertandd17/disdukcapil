<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Home_Controller extends Controller
{
    /**
     * Tampilkan halaman beranda
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Tampilkan halaman profil
     */
    public function profil()
    {
        return view('profil');
    }

    /**
     * Tampilkan halaman berita
     */
    public function berita()
    {
        return view('berita');
    }

    /**
     * Tampilkan halaman layanan
     */
    public function layanan()
    {
        return view('layanan');
    }

    /**
     * Tampilkan halaman kontak
     */
    public function kontak()
    {
        return view('kontak');
    }
}
