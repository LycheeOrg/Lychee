<?php

namespace App\Actions\Album;

use App\DTO\AlbumProtectionPolicy;
use App\Enums\PolicyPropagationEnum;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\Album;
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
	public function do(BaseAlbum $album, AlbumProtectionPolicy $protectionPolicy, bool $shallSetPassword, ?string $password, PolicyPropagationEnum $propagateToChildren): void
	{
		$this->setAlbumProtectionPolicy($album, $protectionPolicy);
		$this->setPassword($album, $shallSetPassword, $password);
		$album->save();

		// Reset permissions for photos
		if ($album->is_public) {
			$album->photos()->update(['photos.is_public' => false]);
		}

		if (!$album instanceof Album) {
			return;
		}

		// Do propagation.
		switch ($propagateToChildren) {
			case PolicyPropagationEnum::cut():
				// We remove the inherit from parent from all the direct descendents
				$album->children()->update([
					'inherits_protection_policy' => false,
				]);
				break;

			case PolicyPropagationEnum::force():
				// we propagate the current parent change to all children.
				// Note that we do not propagate the require_link attribute
				$album->descendants()->update([
					'inherits_protection_policy' => true,
					'is_public' => $album->is_public,
					'is_downloadable' => $album->is_downloadable,
					'grants_full_photo' => $album->grants_full_photo,
					'is_share_button_visible' => $album->is_share_button_visible,
				]);
				if ($album->is_public) {
					$album->all_photos()->update(['photos.is_public' => false]);
				}
				break;

			// Normal refresh of the children
			default:
			case PolicyPropagationEnum::refresh():
				$descendansToUpdate = $album->descendants()->where('inherits_protection_policy', '=', '1')->with('parent')->orderBy('_lft', 'asc')->get();
				foreach ($descendansToUpdate as $descendant) {
					$this->setAlbumPolicyFromParent($descendant);
					$descendant->save();
				}
				break;
			}
	}

	/**
	 * Given a protection policy, apply changes or infer from parent.
	 *
	 * @param BaseAlbum             $album
	 * @param AlbumProtectionPolicy $protectionPolicy
	 *
	 * @return void
	 */
	private function setAlbumProtectionPolicy(BaseAlbum $album, AlbumProtectionPolicy $protectionPolicy): void
	{
		// In order to apply inheritance we must verify:
		// - that it is an album and not a tag album.
		// - and that the album is not at the root.
		$album->inherits_protection_policy = $album instanceof Album && $album->inherits_protection_policy && $album->parent_id !== null;

		$album->requires_link = $protectionPolicy->requiresLink;

		/** @var Album $album : this is is infered, but phpStan does not "remember" it */
		if ($album->inherits_protection_policy) {
			$this->setAlbumPolicyFromParent($album);

			return;
		}

		$album->grants_full_photo = $protectionPolicy->grantsFullPhoto;
		$album->is_public = $protectionPolicy->isPublic;
		$album->is_nsfw = $protectionPolicy->isNSFW;
		$album->is_downloadable = $protectionPolicy->isDownloadable;
		$album->is_share_button_visible = $protectionPolicy->isShareButtonVisible;
	}

	/**
	 * Given an album, inherit the protection policy from the parent.
	 *
	 * @param Album $album
	 *
	 * @return void
	 */
	private function setAlbumPolicyFromParent(Album $album): void
	{
		$album->grants_full_photo = $album->parent->grants_full_photo;
		$album->is_public = $album->parent->is_public;
		$album->is_nsfw = $album->parent->is_nsfw;
		$album->is_downloadable = $album->parent->is_downloadable;
		$album->is_share_button_visible = $album->parent->is_share_button_visible;
	}

	/**
	 * Update password if necessary.
	 *
	 * @param BaseAlbum   $album
	 * @param bool        $shallSetPassword
	 * @param string|null $password
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws FrameworkException
	 */
	private function setPassword(BaseAlbum $album, bool $shallSetPassword, ?string $password): void
	{
		// password is not provided => there is no change
		if (!$shallSetPassword) {
			return;
		}

		// password is null => we remove the password
		if ($password === null) {
			$album->password = null;

			return;
		}

		// password is not null => we update the value with the hash
		try {
			$album->password = bcrypt($password);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Could not hash password', $e);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s hashing component', $e);
		}
	}
}
