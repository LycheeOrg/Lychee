<?php

namespace App\Actions\Album;

use App\DTO\AlbumProtectionPolicy;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Album;
use App\Models\Configs;

class Create extends Action
{
	public function __construct(public readonly int $intendedOwnerId)
	{
		parent::__construct();
	}

	/**
	 * @param string     $title
	 * @param Album|null $parentAlbum
	 *
	 * @return Album
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 */
	public function create(string $title, ?Album $parentAlbum): Album
	{
		$album = new Album();
		$album->title = $title;
		$this->set_parent($album, $parentAlbum);

		// We do not transfer the password
		$album->policy = match (Configs::getValueAsInt('default_album_protection')) {
			1 => AlbumProtectionPolicy::ofDefaultPrivate(),
			2 => AlbumProtectionPolicy::ofDefaultPublic(),
			3 => ($parentAlbum !== null ? AlbumProtectionPolicy::ofBaseAlbum($parentAlbum) : AlbumProtectionPolicy::ofDefaultPrivate()),
			default => AlbumProtectionPolicy::ofDefaultPrivate() // just to be safe of stupid values
		};

		$album->save();

		return $album;
	}

	/**
	 * Setups parent album on album structure.
	 *
	 * @param Album      $album
	 * @param Album|null $parentAlbum
	 *
	 * @throws UnauthenticatedException
	 */
	private function set_parent(Album $album, ?Album $parentAlbum): void
	{
		if ($parentAlbum !== null) {
			// Admin can add sub-albums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parentAlbum->owner_id;
			// Don't set attribute `parent_id` manually, but use specialized
			// methods of the nested set `NodeTrait`.
			$album->appendToNode($parentAlbum);
		} else {
			$album->owner_id = $this->intendedOwnerId;
			$album->makeRoot();
		}
	}
}
