<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use AccessControl;
use App\Actions\ReadAccessFunctions;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Extensions\CustomSort;
use Illuminate\Support\Facades\Hash;

class AlbumFunctions
{
	use CustomSort;

	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @var AlbumFactory
	 */
	private $albumFactory;

	/**
	 * AlbumFunctions constructor.
	 *
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(
		ReadAccessFunctions $readAccessFunctions,
		SymLinkFunctions $symLinkFunctions,
		AlbumFactory $albumFactory
	) {
		$this->readAccessFunctions = $readAccessFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->albumFactory = $albumFactory;
	}

	/**
	 * Provided an password and an album, check if the album can be
	 * unlocked. If yes, unlock all albums with the same password.
	 *
	 * TODO: MOVE
	 */
	public function unlockAlbum(?string $albumid, $password): bool
	{
		switch ($albumid) {
			case 'starred':
			case 'public':
			case 'recent':
			case 'unsorted':
				return false;
			default:
				$album = Album::find($albumid);
				if ($album === null) {
					return false;
				}
				if ($album->public == 1) {
					if ($album->password === '') {
						return true;
					}
					if (AccessControl::has_visible_album($album->id)) {
						return true;
					}
					$password ??= '';
					if (Hash::check($password, $album->password)) {
						$this->unlockAllAlbums($password);

						return true;
					}
				}

				return false;
		}
	}

	/**
	 * Provided an password, add all the albums that the password unlocks.
	 * TODO: MOVE.
	 */
	public function unlockAllAlbums(string $password): void
	{
		// We add all the albums that the password unlocks so that the
		// user is not repeatedly asked to enter the password as they
		// browse through the hierarchy.  This should be safe as the
		// list of such albums is not exposed to the user and is
		// considered as the last access check criteria.
		$albums = Album::whereNotNull('password')->where('password', '!=', '')->get();
		$albumIDs = [];
		foreach ($albums as $album) {
			if (Hash::check($password, $album->password)) {
				$albumIDs[] = $album->id;
			}
		}

		AccessControl::add_visible_albums($albumIDs);
	}
}
