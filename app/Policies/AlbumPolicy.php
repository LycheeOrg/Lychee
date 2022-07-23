<?php

namespace App\Policies;

use App\Models\Album;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlbumPolicy
{
	use HandlesAuthorization;

	/**
	 * Perform pre-authorization checks.
	 *
	 * @param \App\Models\User $user
	 * @param string           $ability
	 *
	 * @return void|bool
	 */
	public function before(?User $user, $ability)
	{
		if (optional($user)->id === 0) {
			return true;
		}
	}

	/**
	 * This gate policy ensures that the Album is owned by current user.
	 * Do note that in case of current user being admin, it will be skipped due to the before method.
	 *
	 * TODO: Check if this is also used in TagAlbums and Smart albums
	 *
	 * @param User|null $user
	 * @param Album     $album
	 *
	 * @return bool
	 */
	public function own(?User $user, Album $album): bool
	{
		return $album->owner_id === optional($user)->id;
	}
}
