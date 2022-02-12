<?php

namespace App\Actions\Album;

use App\DTO\AlbumAccessSettings;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Contracts\Container\BindingResolutionException;

class SetPublic extends Action
{
	/**
	 * @param BaseAlbum           $album
	 * @param AlbumAccessSettings $accessSettings
	 * @param bool                $shallSetPassword
	 * @param string|null         $password
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 * @throws FrameworkException
	 */
	public function do(BaseAlbum $album, AlbumAccessSettings $accessSettings, bool $shallSetPassword, ?string $password): void
	{
		$album->grants_full_photo = $accessSettings->grantsFullPhoto;
		$album->is_public = $accessSettings->isPublic;
		$album->requires_link = $accessSettings->requiresLink;
		$album->is_nsfw = $accessSettings->isNSFW;
		$album->is_downloadable = $accessSettings->isDownloadable;
		$album->is_share_button_visible = $accessSettings->isShareButtonVisible;

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
