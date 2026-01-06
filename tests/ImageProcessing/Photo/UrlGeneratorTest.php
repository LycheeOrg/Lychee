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

namespace Tests\ImageProcessing\Photo;

use App\Models\Configs;
use App\Services\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UrlGeneratorTest extends BaseApiWithDataTest
{
	private UrlGenerator $urlGenerator;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('temporary_image_link_enabled', false);
		Configs::set('temporary_image_link_when_logged_in', false);
		Configs::set('temporary_image_link_when_admin', false);
	}

	public function tearDown(): void
	{
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('temporary_image_link_when_logged_in', false);
		Configs::set('temporary_image_link_when_admin', false);
		parent::tearDown();
	}

	public function testAllFalse(): void
	{
		$this->urlGenerator = resolve(UrlGenerator::class);
		self::assertNull(Auth::user());
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'No user, no signed URL');

		Auth::login($this->userMayUpload1);
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'Logged in user, no signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'Admin user, no signed URL');
		Auth::logout();
	}

	public function testFalseOnlyGuest(): void
	{
		Configs::set('temporary_image_link_enabled', true);
		$this->urlGenerator = resolve(UrlGenerator::class);

		self::assertNull(Auth::user());
		self::assertFalse($this->urlGenerator->shouldNotUseSignedUrl(), 'No user, signed URL');

		Auth::login($this->userMayUpload1);
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'Logged in user, no signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'Admin user, no signed URL');
	}

	public function testFalseOnlyLoggedInAndGuest(): void
	{
		Configs::set('temporary_image_link_enabled', true);
		Configs::set('temporary_image_link_when_logged_in', true);
		$this->urlGenerator = resolve(UrlGenerator::class);

		self::assertNull(Auth::user());
		self::assertFalse($this->urlGenerator->shouldNotUseSignedUrl(), 'No user, signed URL');

		Auth::login($this->userMayUpload1);
		self::assertFalse($this->urlGenerator->shouldNotUseSignedUrl(), 'Logged in user, signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'Admin user, no signed URL');
		Auth::logout();
	}

	public function testFalseEveryone(): void
	{
		Configs::set('temporary_image_link_enabled', true);
		Configs::set('temporary_image_link_when_logged_in', true);
		Configs::set('temporary_image_link_when_admin', true);
		$this->urlGenerator = resolve(UrlGenerator::class);

		self::assertNull(Auth::user());
		self::assertFalse($this->urlGenerator->shouldNotUseSignedUrl(), 'No user, signed URL');

		Auth::login($this->userMayUpload1);
		self::assertFalse($this->urlGenerator->shouldNotUseSignedUrl(), 'Logged in user, signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertFalse($this->urlGenerator->shouldNotUseSignedUrl(), 'Admin user, signed URL');
	}

	public function testTrueEvenIfEveryone(): void
	{
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('temporary_image_link_when_logged_in', true);
		Configs::set('temporary_image_link_when_admin', true);
		$this->urlGenerator = resolve(UrlGenerator::class);

		self::assertNull(Auth::user());
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'No user, no signed URL');

		Auth::login($this->userMayUpload1);
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'Logged in user, no signed URL');
		Auth::logout();

		Auth::login($this->admin);
		self::assertTrue($this->urlGenerator->shouldNotUseSignedUrl(), 'Admin user, no signed URL');
	}
}