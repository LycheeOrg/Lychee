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

namespace Tests\Feature_v2\Gallery;

use App\Models\Configs;
use App\Services\TemporaryLinkSigner;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class TemporaryLinkMacConfigTest extends BaseApiWithDataTest
{
	public function testMacIsEmptyWhenFeatureDisabled(): void
	{
		Configs::set('temporary_image_link_enabled', false);

		$response = $this->getJson('Gallery::getMac');
		$this->assertOk($response);
		$response->assertJson(['mac' => '']);
	}

	public function testMacVerifiesWhenFeatureEnabled(): void
	{
		Configs::set('temporary_image_link_enabled', true);

		$response = $this->getJson('Gallery::getMac');
		$this->assertOk($response);

		/** @var string $mac */
		$mac = $response->json('mac');
		self::assertNotSame('', $mac);
		self::assertTrue(resolve(TemporaryLinkSigner::class)->verify($mac));
	}
}
