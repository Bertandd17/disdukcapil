<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Pengguna_Controller extends Controller
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

    /**
     * Tampilkan halaman layanan mandiri
     */
    public function layanan_mandiri()
    {
        return view('layanan_mandiri');
    }

    /**
     * Tampilkan halaman statistik
     */
    public function statistik()
    {
        return view('statistik');
    }

    /**
     * Tampilkan halaman tracking
     */
    public function tracking()
    {
        return view('tracking');
    }
}
