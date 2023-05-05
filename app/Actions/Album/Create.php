<?php

namespace App\Actions\Album;

use App\Enum\DefaultAlbumProtectionType;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\AccessPermission;
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
		$album->save();

		$defaultProtectionType = Configs::getValueAsEnum('default_album_protection', DefaultAlbumProtectionType::class);

		if ($defaultProtectionType === DefaultAlbumProtectionType::PUBLIC) {
			$album->access_permissions()->saveMany([AccessPermission::ofPublic()]);
		}

		if ($defaultProtectionType === DefaultAlbumProtectionType::INHERIT && $parentAlbum !== null) {
			$album->access_permissions()->saveMany($this->copyPermission($parentAlbum));
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
			$album->owner_id = $this->intendedOwnerId;
			$album->makeRoot();
		}
	}

	/**
	 * Given a parent album, retrieve its access permission and return an array containing copies of them.
	 *
	 * @param Album|null $parentAlbum
	 *
	 * @return array<int,AccessPermission> array of access permissions
	 */
	private function copyPermission(?Album $parentAlbum): array
	{
		$parentPermissions = $parentAlbum->access_permissions;
		$copyPermissions = [];
		foreach ($parentPermissions as $parentPermission) {
			$copyPermissions[] = AccessPermission::ofAccessPermission($parentPermission);
		}

		return $copyPermissions;
	}
}
