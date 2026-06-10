<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AntrianOnlineModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class AntrianOnlinePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('Admin') || $user->hasRole('Keagamaan');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, AntrianOnlineModel $antrian)
    {
        // Admin can view all
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Keagamaan can view their own
        if ($user->hasRole('Keagamaan')) {
            return $antrian->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasRole('Admin') || $user->hasRole('Keagamaan');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, AntrianOnlineModel $antrian)
    {
        // Admin can update all
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Keagamaan can update their own
        if ($user->hasRole('Keagamaan')) {
            return $antrian->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, AntrianOnlineModel $antrian)
    {
        // Only admin can delete
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, AntrianOnlineModel $antrian)
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, AntrianOnlineModel $antrian)
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can verify the antrian.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function verify(User $user, AntrianOnlineModel $antrian)
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can process the antrian.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function process(User $user, AntrianOnlineModel $antrian)
    {
        return $user->hasRole('Admin') || $user->hasRole('Keagamaan');
    }

    /**
     * Determine whether the user can complete the antrian.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AntrianOnlineModel  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function complete(User $user, AntrianOnlineModel $antrian)
    {
        return $user->hasRole('Admin');
    }
}
