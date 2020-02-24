<?php

namespace App\Policies;

use App\Genre;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GenrePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any genres.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function view(User $user, Genre $genre)
    {
        //
    }

    /**
     * Determine whether the user can create genres.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function update(User $user, Genre $genre)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function delete(User $user, Genre $genre)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function restore(User $user, Genre $genre)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the genre.
     *
     * @param  \App\User  $user
     * @param  \App\Genre  $genre
     * @return mixed
     */
    public function forceDelete(User $user, Genre $genre)
    {
        //
    }
}
