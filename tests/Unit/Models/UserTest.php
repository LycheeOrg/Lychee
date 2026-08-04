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

namespace Tests\Unit\Models;

use App\Exceptions\UnauthenticatedException;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laragear\WebAuthn\WebAuthnData;
use Tests\AbstractTestCase;

class UserTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testUsernameReturnsUtf8String(): void
	{
		$user = new User();
		$user->username = 'plain-name';
		self::assertEquals('plain-name', $user->username());
	}

	public function testGetNameAttributeReturnsAdminForBlowfishHashedUsername(): void
	{
		$user = new User();
		$user->username = '$2y$10$abcdefghijklmnopqrstuv';
		$user->display_name = 'Should be ignored';
		self::assertEquals('Admin', $user->name);
	}

	public function testGetNameAttributePrefersDisplayNameOverUsername(): void
	{
		$user = new User();
		$user->username = 'jdoe';
		$user->display_name = 'John Doe';
		self::assertEquals('John Doe', $user->name);
	}

	public function testGetNameAttributeFallsBackToUsernameWithoutDisplayName(): void
	{
		$user = new User();
		$user->username = 'jdoe';
		$user->display_name = null;
		self::assertEquals('jdoe', $user->name);
	}

	public function testWebAuthnDataUsesEmailWhenPresent(): void
	{
		$user = new User();
		$user->username = 'jdoe';
		$user->display_name = 'John Doe';
		$user->email = 'jdoe@example.com';

		$data = $user->webAuthnData();

		self::assertInstanceOf(WebAuthnData::class, $data);
	}

	public function testDeleteWithoutOwnedContentSkipsReassignment(): void
	{
		$user = User::factory()->create();

		self::assertTrue($user->delete());
		self::assertNull(User::find($user->id));
	}

	public function testDeleteThrowsWhenOwnedContentExistsAndNoAuthenticatedUser(): void
	{
		$owner = User::factory()->create();
		Album::factory()->as_root()->owned_by($owner)->create();

		$this->assertThrows(fn () => $owner->delete(), UnauthenticatedException::class);
	}

	public function testDeleteReassignsOwnershipToAuthenticatedUser(): void
	{
		$owner = User::factory()->create();
		$new_owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();
		$photo = Photo::factory()->owned_by($owner)->create();

		$this->actingAs($new_owner);
		self::assertTrue($owner->delete());

		self::assertEquals($new_owner->id, $album->fresh()->owner_id);
		self::assertEquals($new_owner->id, $photo->fresh()->owner_id);
		self::assertNull(User::find($owner->id));
	}
}
