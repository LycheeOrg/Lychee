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

namespace Tests\Feature_v3\ClientCache;

use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * The client cache flag is rendered into the HTML page.
 */
class ClientCacheMetaTest extends BaseApiWithDataTest
{
	public function tearDown(): void
	{
		config(['features.client-cache' => true]);
		parent::tearDown();
	}

	public function testEnabledByDefault(): void
	{
		$this->assertMetaTag('1');
	}

	public function testDisabled(): void
	{
		config(['features.client-cache' => false]);

		$this->assertMetaTag('0');
	}

	private function assertMetaTag(string $content): void
	{
		$response = $this->get('/gallery');

		$this->assertOk($response);
		$response->assertSee('<meta name="lychee-client-cache" content="' . $content . '">', false);
	}
}
