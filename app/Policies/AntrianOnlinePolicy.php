<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Antrian_Online_Model;
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
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Antrian_Online_Model $antrian)
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
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Antrian_Online_Model $antrian)
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
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Antrian_Online_Model $antrian)
    {
        // Only admin can delete
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Antrian_Online_Model $antrian)
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Antrian_Online_Model $antrian)
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can verify the antrian.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function verify(User $user, Antrian_Online_Model $antrian)
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can process the antrian.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function process(User $user, Antrian_Online_Model $antrian)
    {
        return $user->hasRole('Admin') || $user->hasRole('Keagamaan');
    }

    /**
     * Determine whether the user can complete the antrian.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Antrian_Online_Model  $antrian
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function complete(User $user, Antrian_Online_Model $antrian)
    {
        return $user->hasRole('Admin');
    }
}
