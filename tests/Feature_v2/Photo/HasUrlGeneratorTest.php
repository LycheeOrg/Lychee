<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v2\Photo;

use App\Models\Configs;
use App\Models\Extensions\HasUrlGenerator;
use Illuminate\Support\Facades\Auth;
use Tests\Feature_v2\Base\BaseApiV2Test;

class HasUrlGeneratorTest extends BaseApiV2Test
{
	use HasUrlGenerator;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('temporary_image_link_enabled', false);
		Configs::set('temporary_image_link_when_logged_in', false);
		Configs::set('temporary_image_link_when_admin', false);
		Configs::invalidateCache();
	}

	public function tearDown(): void
	{
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('temporary_image_link_when_logged_in', false);
		Configs::set('temporary_image_link_when_admin', false);
		Configs::invalidateCache();

		parent::tearDown();
	}

	public function testAllFalse(): void
	{
		self::assertNull(Auth::user());
		self::assertTrue(self::shouldNotUseSignedUrl(), 'No user, no signed URL');

		Auth::login($this->userMayUpload1);
		self::assertTrue(self::shouldNotUseSignedUrl(), 'Logged in user, no signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue(self::shouldNotUseSignedUrl(), 'Admin user, no signed URL');
		Auth::logout();
	}

	public function testFalseOnlyGuest(): void
	{
		Configs::set('temporary_image_link_enabled', true);
		Configs::invalidateCache();

		self::assertNull(Auth::user());
		self::assertFalse(self::shouldNotUseSignedUrl(), 'No user, signed URL');

		Auth::login($this->userMayUpload1);
		self::assertTrue(self::shouldNotUseSignedUrl(), 'Logged in user, no signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue(self::shouldNotUseSignedUrl(), 'Admin user, no signed URL');
	}

	public function testFalseOnlyLoggedInAndGuest(): void
	{
		Configs::set('temporary_image_link_enabled', true);
		Configs::set('temporary_image_link_when_logged_in', true);
		Configs::invalidateCache();

		self::assertNull(Auth::user());
		self::assertFalse(self::shouldNotUseSignedUrl(), 'No user, signed URL');

		Auth::login($this->userMayUpload1);
		self::assertFalse(self::shouldNotUseSignedUrl(), 'Logged in user, signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue(self::shouldNotUseSignedUrl(), 'Admin user, no signed URL');
		Auth::logout();
	}

	public function testFalseEveryone(): void
	{
		Configs::set('temporary_image_link_enabled', true);
		Configs::set('temporary_image_link_when_logged_in', true);
		Configs::set('temporary_image_link_when_admin', true);
		Configs::invalidateCache();

		self::assertNull(Auth::user());
		self::assertFalse(self::shouldNotUseSignedUrl(), 'No user, signed URL');

		Auth::login($this->userMayUpload1);
		self::assertFalse(self::shouldNotUseSignedUrl(), 'Logged in user, signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertFalse(self::shouldNotUseSignedUrl(), 'Admin user, signed URL');
	}

	public function testTrueEvenIfEveryone(): void
	{
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('temporary_image_link_when_logged_in', true);
		Configs::set('temporary_image_link_when_admin', true);
		Configs::invalidateCache();

		self::assertNull(Auth::user());
		self::assertTrue(self::shouldNotUseSignedUrl(), 'No user, no signed URL');

		Auth::login($this->userMayUpload1);
		self::assertTrue(self::shouldNotUseSignedUrl(), 'Logged in user, no signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue(self::shouldNotUseSignedUrl(), 'Admin user, no signed URL');
	}
}