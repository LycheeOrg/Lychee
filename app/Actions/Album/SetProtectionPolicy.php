<?php

namespace App\Actions\Album;

use App\DTO\AlbumProtectionPolicy;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class SetProtectionPolicy.
 */
class SetProtectionPolicy extends Action
{
	/**
	 * @param BaseAlbum             $album
	 * @param AlbumProtectionPolicy $protectionPolicy
	 * @param bool                  $shallSetPassword
	 * @param string|null           $password
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 * @throws FrameworkException
	 */
	public function do(BaseAlbum $album, AlbumProtectionPolicy $protectionPolicy, bool $shallSetPassword, ?string $password): void
	{
		$album->grants_full_photo = $protectionPolicy->grantsFullPhoto;
		$album->is_public = $protectionPolicy->isPublic;
		$album->requires_link = $protectionPolicy->requiresLink;
		$album->is_nsfw = $protectionPolicy->isNSFW;
		$album->is_downloadable = $protectionPolicy->isDownloadable;
		$album->is_share_button_visible = $protectionPolicy->isShareButtonVisible;

		// Set password if provided
		if ($shallSetPassword) {
			// password is provided => there is a change
			if ($password !== null) {
				// password is not null => we update the value with the hash
				try {
					$album->password = bcrypt($password);
				} catch (\InvalidArgumentException $e) {
					throw new InvalidPropertyException('Could not hash password', $e);
				} catch (BindingResolutionException $e) {
					throw new FrameworkException('Laravel\'s hashing component', $e);
				}
			} else {
				// we remove the password
				$album->password = null;
			}
		}

		$album->save();

		// Reset permissions for photos
		if ($album->is_public) {
			$album->photos()->update(['photos.is_public' => false]);
		}
	}
}
