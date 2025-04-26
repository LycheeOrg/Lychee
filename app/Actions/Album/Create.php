<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Constants\AccessPermissionConstants as APC;
use App\Enum\DefaultAlbumProtectionType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnexpectedException;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;

class Create
{
	public function __construct(public readonly int $intended_owner_id)
	{
	}

	/**
	 * @param string     $title
	 * @param Album|null $parent_album
	 *
	 * @return Album
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 */
	public function create(string $title, ?Album $parent_album): Album
	{
		$album = new Album();
		$album->title = $title;
		$this->set_parent($album, $parent_album);
		$album->save();
		$this->set_permissions($album, $parent_album);
		$this->setStatistics($album);

		return $album;
	}

	/**
	 * Setups parent album on album structure.
	 *
	 * @param Album      $album
	 * @param Album|null $parent_album
	 *
	 * @throws UnauthenticatedException
	 */
	private function set_parent(Album $album, ?Album $parent_album): void
	{
		if ($parent_album !== null) {
			// Admin can add sub-albums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parent_album->owner_id;
			// Don't set attribute `parent_id` manually, but use specialized
			// methods of the nested set `NodeTrait`.
			$album->appendToNode($parent_album);
		} else {
			$album->owner_id = $this->intended_owner_id;
			$album->makeRoot();
		}
	}

	/**
	 * Set up the permissions.
	 *
	 * @param Album      $album
	 * @param Album|null $parent_album
	 *
	 * @return void
	 *
	 * @throws UnexpectedException
	 * @throws ConfigurationKeyMissingException
	 */
	private function set_permissions(Album $album, ?Album $parent_album): void
	{
		$default_protection_type = Configs::getValueAsEnum('default_album_protection', DefaultAlbumProtectionType::class);

		if ($default_protection_type === DefaultAlbumProtectionType::PUBLIC) {
			$album->access_permissions()->saveMany([AccessPermission::ofPublic()]);
		}

		if ($default_protection_type === DefaultAlbumProtectionType::INHERIT && $parent_album !== null) {
			$album->access_permissions()->saveMany($this->copyPermission($parent_album));
		}

		$this->grantFullPermissionsToNewOwner($album);
	}

	/**
	 * Given a parent album, retrieve its access permission and return an array containing copies of them.
	 *
	 * @param Album|null $parent_album
	 *
	 * @return array<int,AccessPermission> array of access permissions
	 */
	private function copyPermission(?Album $parent_album): array
	{
		$parent_permissions = $parent_album->access_permissions;
		$copy_permissions = [];
		foreach ($parent_permissions as $parent_permission) {
			$copy_permissions[] = AccessPermission::ofAccessPermission($parent_permission);
		}

		return $copy_permissions;
	}

	/**
	 * If album is created by someone who has the album shared with.
	 * We need to give access all to that person.
	 *
	 * @param Album $album
	 *
	 * @return void
	 */
	private function grantFullPermissionsToNewOwner(Album $album)
	{
		if ($album->owner_id === $this->intended_owner_id) {
			return;
		}

		$album->access_permissions()
			->where(APC::USER_ID, '=', $this->intended_owner_id)
			->where(APC::BASE_ALBUM_ID, '=', $album->id)
			->delete();

		$access_perm = AccessPermission::withGrantFullPermissionsToUser($this->intended_owner_id);

		$album->access_permissions()->save($access_perm);
	}

	private function setStatistics(Album $album): void
	{
		$album->statistics()->create([
			'album_id' => $album->id,
			'visit_count' => 0,
			'download_count' => 0,
			'favourite_count' => 0,
			'shared_count' => 0,
		]);
	}
}
