<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Constants\AccessPermissionConstants as APC;
use App\Exceptions\UnauthorizedException;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Hash;

class Unlock extends Action
{
	private AlbumPolicy $albumPolicy;

	public function __construct()
	{
		parent::__construct();
		$this->albumPolicy = resolve(AlbumPolicy::class);
	}

	/**
	 * Tries to unlock the given album with the given password.
	 *
	 * If the password is correct, then all albums which can be unlocked with
	 * the same password are unlocked, too.
	 *
	 * @param BaseAlbum $album
	 * @param string    $password
	 *
	 * @throws UnauthorizedException
	 */
	public function do(BaseAlbum $album, string $password): void
	{
		if ($album->public_permissions() !== null) {
			$album_password = $album->public_permissions()->password;
			if (
				$album_password === null ||
				$album_password === '' ||
				$this->albumPolicy->isUnlocked($album)
			) {
				return;
			}
			if (Hash::check($password, $album_password)) {
				$this->propagate($password);

				return;
			}
			throw new UnauthorizedException('Password is invalid');
		}

		throw new UnauthorizedException('Album is not enabled for password-based access');
	}

	/**
	 * Provided a password, add all the albums that the password unlocks.
	 */
	private function propagate(string $password): void
	{
		// We add all the albums that the password unlocks so that the
		// user is not repeatedly asked to enter the password as they
		// browse through the hierarchy.  This should be safe as the
		// list of such albums is not exposed to the user and is
		// considered as the last access check criteria.
		$albums = BaseAlbumImpl::query()
			->select(['base_albums.id', 'base_albums.owner_id', APC::PASSWORD])
			->join(APC::ACCESS_PERMISSIONS, 'base_album_id', '=', 'base_albums.id', 'inner')
			->whereNull(APC::ACCESS_PERMISSIONS . '.user_id')
			->whereNotNull(APC::PASSWORD)
			->get();
		/** @var BaseAlbumImpl $album */
		foreach ($albums as $album) {
			if (Hash::check($password, $album->password)) {
				$this->albumPolicy->unlock($album);
			}
		}
	}
}
