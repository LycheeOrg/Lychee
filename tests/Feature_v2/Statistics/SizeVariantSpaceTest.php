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

namespace Tests\Feature_v2\Statistics;

use App\Enum\SizeVariantType;
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiV2Test;

class SizeVariantSpaceTest extends BaseApiV2Test
{
	public function testSizeVariantSpaceUnauthorized(): void
	{
		$response = $this->getJson('Statistics::sizeVariantSpace');
		$this->assertSupporterRequired($response);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->getJson('Statistics::sizeVariantSpace');
		$this->assertUnauthorized($response);
	}

	public function testSizeVariantSpaceAuthorized(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->userMayUpload1)->getJson('Statistics::sizeVariantSpace');
		$this->assertOk($response);
		self::assertCount(7, $response->json());
		self::assertEquals(SizeVariantType::ORIGINAL->value, $response->json()[0]['type']);
		self::assertEquals(SizeVariantType::MEDIUM2X->value, $response->json()[1]['type']);
		self::assertEquals(SizeVariantType::MEDIUM->value, $response->json()[2]['type']);
		self::assertEquals(SizeVariantType::SMALL2X->value, $response->json()[3]['type']);
		self::assertEquals(SizeVariantType::SMALL->value, $response->json()[4]['type']);
		self::assertEquals(SizeVariantType::THUMB2X->value, $response->json()[5]['type']);
		self::assertEquals(SizeVariantType::THUMB->value, $response->json()[6]['type']);
	}
}