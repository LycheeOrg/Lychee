<?php

namespace App\Actions\Album;

use App\DTO\AlbumProtectionPolicy;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class Create extends Action
{
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
		$album->save();

		switch (Configs::getValueAsInt('default_album_protection')) {
			case 2:
				$protectionPolicy = new AlbumProtectionPolicy(
					is_public: true,
					is_link_required: false,
					is_nsfw: false,
					grants_full_photo_access: true,
					grants_download: true,
					is_password_required: false,
				);
				break;
			case 3:
				if ($parentAlbum !== null) {
					$protectionPolicy = AlbumProtectionPolicy::ofBaseAlbum($parentAlbum);
				} else {
					$protectionPolicy = new AlbumProtectionPolicy(
						is_public: false,
						is_link_required: false,
						is_nsfw: false,
						grants_full_photo_access: true,
						grants_download: true,
						is_password_required: false,
					);
				}
				break;
			default:
				$protectionPolicy = new AlbumProtectionPolicy(
					is_public: false,
					is_link_required: false,
					is_nsfw: false,
					grants_full_photo_access: true,
					grants_download: true,
					is_password_required: false,
				);
				break;
		}

		$setter = new SetProtectionPolicy();
		$setter->do($album, $protectionPolicy, false, null);

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
			/** @var int $userId */
			$userId = Auth::id() ?? throw new UnauthenticatedException();
			$album->owner_id = $userId;
			$album->makeRoot();
		}
	}
}
