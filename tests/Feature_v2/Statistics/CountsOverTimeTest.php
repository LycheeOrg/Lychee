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

use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiV2Test;

class CountsOverTimeTest extends BaseApiV2Test
{
	public function testCountsOverTimeUnauthorized(): void
	{
		$response = $this->getJson('Statistics::getCountsOverTime');
		$this->assertSupporterRequired($response);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->getJson('Statistics::getCountsOverTime');
		$this->assertUnprocessable($response);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->getJsonWithData('Statistics::getCountsOverTime', [
			'type' => 'taken_at',
		]);
		$this->assertUnauthorized($response);
	}

	public function testCountsOverTimeAdmin(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->getJsonWithData('Statistics::getCountsOverTime', [
			'type' => 'created_at',
		]);
		$this->assertOk($response);
		self::assertCount(6, $response->json());

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->getJsonWithData('Statistics::getCountsOverTime', [
			'type' => 'taken_at',
		]);
		$this->assertOk($response);
		self::assertCount(6, $response->json());

		// dd($response->json());
		// self::assertEquals($this->userMayUpload1->username(), $response->json()[0]['username']);
	}
}