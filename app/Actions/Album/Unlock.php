<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Exceptions\UnauthorizedException;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class Unlock
{
	/** Encrypted passwords which successfully unlocked an album in this session. */
	public const REMEMBERED_PASSWORDS_SESSION_KEY = 'unlock_remembered_passwords';

	/** IDs of albums which none of the remembered passwords unlock. */
	public const REJECTED_ALBUMS_SESSION_KEY = 'unlock_rejected_albums';

	public function __construct(
		private AlbumPolicy $album_policy,
	) {
	}

	/**
	 * Tries to unlock the given album with the given password.
	 *
	 * If the password is correct and unlock propagation is enabled, the
	 * password is remembered so that other albums protected by the same
	 * password are unlocked as they are opened
	 * (see {@see self::tryRememberedPasswords()}).
	 *
	 * @param BaseAlbum $album
	 * @param string    $password
	 *
	 * @throws UnauthorizedException
	 */
	public function do(BaseAlbum $album, #[\SensitiveParameter] string $password): void
	{
		if ($album->public_permissions() !== null) {
			$album_password = $album->public_permissions()->password;
			if (
				$album_password === null ||
				$album_password === '' ||
				$this->album_policy->isUnlocked($album)
			) {
				return;
			}
			if (Hash::check($password, $album_password)) {
				$this->album_policy->unlock($album); // unlock the album

				// remember the password to propagate the unlock lazily
				$this->rememberPassword($password);

				return;
			}
			throw new UnauthorizedException('Password is invalid');
		}

		throw new UnauthorizedException('Album is not enabled for password-based access');
	}

	/**
	 * Tries to unlock the given album with the passwords remembered in
	 * this session.
	 *
	 * Propagation is lazy: instead of checking every password-protected
	 * album as soon as a password is entered, an album is only checked
	 * when it is opened. An album which none of the remembered passwords
	 * unlock is not checked again until a new password is remembered.
	 *
	 * @return bool true if the album has been unlocked
	 */
	public function tryRememberedPasswords(BaseAlbum $album): bool
	{
		$album_password = $album->public_permissions()?->password;
		if (
			!$this->isPropagationEnabled() ||
			$album_password === null ||
			$album_password === '' ||
			$this->album_policy->isUnlocked($album) ||
			in_array($album->id, Session::get(self::REJECTED_ALBUMS_SESSION_KEY, []), true)
		) {
			return false;
		}

		foreach ($this->getRememberedPasswords() as $password) {
			if (Hash::check($password, $album_password)) {
				$this->album_policy->unlock($album);

				return true;
			}
		}

		Session::push(self::REJECTED_ALBUMS_SESSION_KEY, $album->id);

		return false;
	}

	/**
	 * Remember the password (encrypted) for lazy propagation.
	 *
	 * Albums rejected so far are forgotten, as the new password might
	 * unlock them.
	 */
	private function rememberPassword(#[\SensitiveParameter] string $password): void
	{
		if (!$this->isPropagationEnabled()) {
			return;
		}

		/** @phpstan-ignore sensitiveParameter.propagation (the password is encrypted here) */
		Session::push(self::REMEMBERED_PASSWORDS_SESSION_KEY, Crypt::encryptString($password));
		Session::forget(self::REJECTED_ALBUMS_SESSION_KEY);
	}

	/**
	 * @return string[]
	 */
	private function getRememberedPasswords(): array
	{
		return array_map(
			fn (string $encrypted) => Crypt::decryptString($encrypted),
			Session::get(self::REMEMBERED_PASSWORDS_SESSION_KEY, [])
		);
	}

	private function isPropagationEnabled(): bool
	{
		return app(ConfigManager::class)->getValueAsBool('enable_propagate_unlock_option');
	}
}
