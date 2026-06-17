<?php

namespace App\Http\Controllers\Keagamaan\Concerns;

use Illuminate\Support\Facades\DB;

trait ResolvesKeagamaanAccount
{
    /**
     * Profil keagamaan user login: tempat ibadah + jenis agama.
     */
    protected function resolveKeagamaanAccount(): object
    {
        $keagamaan = DB::table('keagamaan as k')
            ->join('jenis_keagamaan as j', 'k.jenis_keagamaan_id', '=', 'j.jenis_keagamaan_id')
            ->join('users as u', 'k.user_id', '=', 'u.id')
            ->where('k.user_id', auth()->id())
            ->select(
                'k.keagamaan_id',
                'k.user_id',
                'k.jenis_keagamaan_id',
                'k.alamat',
                'k.status',
                'j.nama_jenis_keagamaan',
                'u.name as nama_tempat_ibadah'
            )
            ->first();

        if (!$keagamaan) {
            abort(403, 'Akun ini tidak terhubung ke data keagamaan.');
        }

        return $keagamaan;
    }

    protected function resolveKeagamaanId(): string
    {
        return $this->resolveKeagamaanAccount()->keagamaan_id;
    }
}
