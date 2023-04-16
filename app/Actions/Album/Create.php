<?php

namespace App\Actions\Album;

use App\Enum\DefaultAlbumProtectionType;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\AccessPermission;
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

		$defaultProtectionType = Configs::getValueAsEnum('default_album_protection', DefaultAlbumProtectionType::class);

		if ($defaultProtectionType === DefaultAlbumProtectionType::PUBLIC) {
			// TODO: DOUBLE CHECK
			// @phpstan-ignore-next-line
			$album->access_permissions()->attach(AccessPermission::ofPublic());
		}

		if ($defaultProtectionType === DefaultAlbumProtectionType::INHERIT && $parentAlbum !== null) {
			$parentPermissions = $parentAlbum->access_permissions;
			$copyPermissions = [];
			foreach ($parentPermissions as $parentPermission) {
				$copyPermissions[] = AccessPermission::ofAccessPermission($parentPermission);
			}
			// TODO: DOUBLE CHECK
			// @phpstan-ignore-next-line
			$album->access_permissions()->sync($copyPermissions);
		}

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
