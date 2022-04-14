<?php

namespace App\Actions\Album;

use App\Actions\AlbumAuthorisationProvider;
use App\Exceptions\UnauthorizedException;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Support\Facades\Hash;

class Unlock extends Action
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct()
	{
		parent::__construct();
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
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
		if ($album->is_public) {
			if (
				empty($album->password) ||
				$this->albumAuthorisationProvider->isUnlocked($album)
			) {
				return;
			}
			if (Hash::check($password, $album->password)) {
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
			->where('is_public', '=', true)
			->whereNotNull('password')
			->get();
		/** @var BaseAlbumImpl $album */
		foreach ($albums as $album) {
			if (Hash::check($password, $album->password)) {
				$this->albumAuthorisationProvider->unlock($album);
			}
		}
	}
}
