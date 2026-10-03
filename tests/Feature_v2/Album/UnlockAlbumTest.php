<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Album;

use App\Actions\Album\Unlock;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Hash;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UnlockAlbumTest extends BaseApiWithDataTest
{
	private const PASSWORD = 'shared-password';
	private const OTHER_PASSWORD = 'other-password';

	/**
	 * album4 and its child subAlbum4 are both public and protected by the
	 * same password, each with its own separately salted hash.
	 */
	public function setUp(): void
	{
		parent::setUp();
		$this->setPassword($this->perm4, self::PASSWORD);
		$this->setPassword($this->perm44, self::PASSWORD);
	}

	public function testWrongPasswordIsRejected(): void
	{
		Configs::set('enable_propagate_unlock_option', '1');

		$response = $this->postJson('Album::unlock', ['album_id' => $this->album4->id, 'password' => 'wrong-password']);
		$this->assertForbidden($response);

		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		$this->assertUnauthorized($response);
		self::assertNull(session(Unlock::REMEMBERED_PASSWORDS_SESSION_KEY));
	}

	public function testUnlockWithoutPropagationKeepsChildLocked(): void
	{
		Configs::set('enable_propagate_unlock_option', '0');

		$response = $this->postJson('Album::unlock', ['album_id' => $this->album4->id, 'password' => self::PASSWORD]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		$this->assertOk($response);

		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->subAlbum4->id]);
		$this->assertUnauthorized($response);
		self::assertNull(session(Unlock::REMEMBERED_PASSWORDS_SESSION_KEY));
	}

	public function testUnlockWithPropagationUnlocksChildOnlyWhenOpened(): void
	{
		Configs::set('enable_propagate_unlock_option', '1');

		$response = $this->postJson('Album::unlock', ['album_id' => $this->album4->id, 'password' => self::PASSWORD]);
		$this->assertNoContent($response);

		// Propagation is lazy: the child is not unlocked up front.
		self::assertContains($this->album4->id, AlbumPolicy::getUnlockedAlbumIDs());
		self::assertNotContains($this->subAlbum4->id, AlbumPolicy::getUnlockedAlbumIDs());

		// Opening the child unlocks it with the remembered password.
		$response = $this->getJsonWithData('Album::head', ['album_id' => $this->subAlbum4->id]);
		$this->assertOk($response);
		self::assertContains($this->subAlbum4->id, AlbumPolicy::getUnlockedAlbumIDs());
	}

	public function testRememberedPasswordDoesNotUnlockAlbumWithDifferentPassword(): void
	{
		Configs::set('enable_propagate_unlock_option', '1');
		$album5 = $this->createPasswordAlbum(self::OTHER_PASSWORD);
		$album6 = $this->createPasswordAlbum(self::OTHER_PASSWORD);

		$response = $this->postJson('Album::unlock', ['album_id' => $this->album4->id, 'password' => self::PASSWORD]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album::head', ['album_id' => $album5->id]);
		$this->assertUnauthorized($response);
		self::assertContains($album5->id, session(Unlock::REJECTED_ALBUMS_SESSION_KEY));

		// Entering the other password elsewhere makes album5 eligible again.
		$response = $this->postJson('Album::unlock', ['album_id' => $album6->id, 'password' => self::OTHER_PASSWORD]);
		$this->assertNoContent($response);

		$response = $this->getJsonWithData('Album::head', ['album_id' => $album5->id]);
		$this->assertOk($response);
	}

	public function testRawPasswordIsNotStoredInSession(): void
	{
		Configs::set('enable_propagate_unlock_option', '1');

		$response = $this->postJson('Album::unlock', ['album_id' => $this->album4->id, 'password' => self::PASSWORD]);
		$this->assertNoContent($response);

		self::assertCount(1, session(Unlock::REMEMBERED_PASSWORDS_SESSION_KEY));
		self::assertStringNotContainsString(self::PASSWORD, json_encode(session()->all()));
	}

	private function setPassword(AccessPermission $permission, string $password): void
	{
		$permission->password = Hash::make($password);
		$permission->save();
	}

	private function createPasswordAlbum(string $password): Album
	{
		$album = Album::factory()->as_root()->owned_by($this->userLocked)->create();
		$permission = AccessPermission::factory()->public()->visible()->for_album($album)->create();
		$this->setPassword($permission, $password);

		return $album;
	}
}
