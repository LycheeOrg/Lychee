<?php

namespace App\Actions\Album;

use App\Actions\AlbumAuthorisationProvider;
use App\Models\BaseAlbumImpl;
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
	 * @param string $albumID
	 * @param string $password
	 *
	 * @return bool true on success, false if password was wrong
	 */
	public function do(string $albumID, string $password): bool
	{
		if ($this->albumFactory->isBuiltInSmartAlbum($albumID)) {
			return false;
		}

		$album = $this->albumFactory->findModelOrFail($albumID);
		if ($album->public) {
			if (
				empty($album->password) ||
				$this->albumAuthorisationProvider->isAlbumUnlocked($album->id)
			) {
				return true;
			}
			if (Hash::check($password, $album->password)) {
				$this->propagate($password);

				return true;
			}
		}

		return false;
	}

	/**
	 * Provided a password, add all the albums that the password unlocks.
	 */
	public function propagate(string $password): void
	{
		// We add all the albums that the password unlocks so that the
		// user is not repeatedly asked to enter the password as they
		// browse through the hierarchy.  This should be safe as the
		// list of such albums is not exposed to the user and is
		// considered as the last access check criteria.
		$albums = BaseAlbumImpl::query()
			->where('public', '=', true)
			->whereNotNull('password')
			->get();
		/** @var BaseAlbumImpl $album */
		foreach ($albums as $album) {
			if (Hash::check($password, $album->password)) {
				$this->albumAuthorisationProvider->unlockAlbum($album->id);
			}
		}
	}
}
