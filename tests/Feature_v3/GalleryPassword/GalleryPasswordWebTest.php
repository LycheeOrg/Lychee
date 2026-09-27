<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\GalleryPassword;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use App\Services\GalleryLockState;
use Illuminate\Support\Facades\Hash;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 074 – the gallery HTML page must not leak album metadata while locked (S-074-12).
 */
class GalleryPasswordWebTest extends BaseApiWithDataTest
{
	private string $initial_value = '';
	private string $stored_hash = '';

	public function setUp(): void
	{
		parent::setUp();
		$this->initial_value = strval(Configs::query()->where('key', '=', 'gallery_password')->value('value'));
		$this->stored_hash = Hash::make('open sesame');
		Configs::set('gallery_password', $this->stored_hash);
	}

	public function tearDown(): void
	{
		Configs::query()->where('key', '=', 'gallery_password')->update(['value' => $this->initial_value]);
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	public function testLockedPageHasNoAlbumMetadata(): void
	{
		$response = $this->get('/gallery/' . $this->album4->id);

		$this->assertOk($response);
		$response->assertDontSee($this->album4->title, false);
	}

	public function testLockedPageDoesNotRevealAlbumExistence(): void
	{
		$this->assertOk($this->get('/gallery/aaaaaaaaaaaaaaaaaaaaaaaa'));
	}

	public function testUnlockedPageHasAlbumMetadata(): void
	{
		$response = $this->withCookie(GalleryLockState::COOKIE_NAME, resolve(GalleryLockState::class)->makeCookieValue($this->stored_hash, time() + 60))
			->get('/gallery/' . $this->album4->id);

		$this->assertOk($response);
		$response->assertSee($this->album4->title, false);
	}
}
