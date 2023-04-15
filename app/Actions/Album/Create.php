<?php

namespace App\Actions\Album;

use App\Constants\AccessPermissionConstants as APC;
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
			$album->access_permissions()->attach(new AccessPermission([
				APC::IS_LINK_REQUIRED => false,
				APC::GRANTS_FULL_PHOTO_ACCESS => Configs::getValueAsBool('grants_full_photo_access'),
				APC::GRANTS_DOWNLOAD => Configs::getValueAsBool('grants_download'),
				APC::GRANTS_UPLOAD => false,
				APC::GRANTS_EDIT => false,
				APC::GRANTS_DELETE => false,
				APC::PASSWORD => null,
			]));
		}

		if ($defaultProtectionType === DefaultAlbumProtectionType::INHERIT && $parentAlbum !== null) {
			$parentPermissions = $parentAlbum->access_permissions;
			$copyPermissions = [];
			foreach ($parentPermissions as $parentPermission) {
				$copyPermissions[] = new AccessPermission([
					APC::IS_LINK_REQUIRED => $parentPermission->is_link_required,
					APC::GRANTS_FULL_PHOTO_ACCESS => $parentPermission->grants_full_photo_access,
					APC::GRANTS_DOWNLOAD => $parentPermission->grants_download,
					APC::GRANTS_UPLOAD => $parentPermission->grants_download,
					APC::GRANTS_EDIT => $parentPermission->grants_edit,
					APC::GRANTS_DELETE => $parentPermission->grants_delete,
				]);
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
