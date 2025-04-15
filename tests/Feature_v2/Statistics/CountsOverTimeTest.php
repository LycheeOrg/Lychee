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

use Tests\Feature_v2\Base\BaseApiV2Test;

class CountsOverTimeTest extends BaseApiV2Test
{
	public function testCountsOverTimeUnauthorized(): void
	{
		$response = $this->getJson('Statistics::getCountsOverTime');
		$this->assertSupporterRequired($response);

		$this->requireSe();
		$response = $this->getJson('Statistics::getCountsOverTime');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Statistics::getCountsOverTime', [
			'type' => 'taken_at',
		]);
		$this->assertUnauthorized($response);
		$this->resetSe();
	}

	public function testCountsOverTimeAdmin(): void
	{
		$this->requireSe();
		$response = $this->actingAs($this->admin)->getJsonWithData('Statistics::getCountsOverTime', [
			'type' => 'created_at',
		]);
		$this->assertOk($response);
		self::assertCount(6, $response->json());

		$response = $this->actingAs($this->admin)->getJsonWithData('Statistics::getCountsOverTime', [
			'type' => 'taken_at',
		]);
		$this->assertOk($response);
		self::assertCount(6, $response->json());
		$this->resetSe();
	}
}