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

namespace Tests\Feature_v2;

use App\Models\Configs;
use Illuminate\Support\Facades\URL;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class SecureImageLinksTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set('temporary_image_link_enabled', '1');
		Configs::invalidateCache();
	}

	public function tearDown(): void
	{
		Configs::set('temporary_image_link_enabled', '0');
		Configs::set('secure_image_link_enabled', '0');
		Configs::invalidateCache();
		parent::tearDown();
	}

	public function testTemporaryImage(): void
	{
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$url = $response->json('resource.photos.0.size_variants.medium.url');
		$this->assertStringContainsString('/image/medium/', $url);

		$response = $this->get($url);
		$this->assertNotFound($response);
		$response->assertSeeText('File not found'); // We mocked the file !

		$unsigned_url = explode('?', $url)[0];
		$response = $this->get($unsigned_url);
		$this->assertForbidden($response);
	}

	public function testEncryptedImages(): void
	{
		Configs::set('secure_image_link_enabled', '1');
		Configs::invalidateCache();

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$url = $response->json('resource.photos.0.size_variants.medium.url');
		$this->assertStringContainsString('/image/', $url);

		$response = $this->get($url);
		$this->assertNotFound($response);
		$response->assertSeeText('File not found'); // We mocked the file !

		$unsigned_url = explode('?', $url)[0];
		$response = $this->get($unsigned_url);
		$this->assertForbidden($response);
	}

	public function testBrokenEncryption(): void
	{
		Configs::set('secure_image_link_enabled', '1');
		Configs::invalidateCache();

		$broken_url = URL::temporarySignedRoute('image', now()->addSeconds(10), ['path' => 'broken_path']);
		$response = $this->get($broken_url);
		$this->assertForbidden($response);
		$response->assertSeeText('Invalid payload');
	}
}