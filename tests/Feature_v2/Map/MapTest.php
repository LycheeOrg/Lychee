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

namespace Tests\Feature_v2\Map;

use Tests\Feature_v2\Base\BaseApiV2Test;

class MapTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJson('Map::provider');
		$this->assertOk($response);

		$response = $this->getJson('Map');
		$this->assertUnauthorized($response);

		$response = $this->getJsonWithData('Map', ['album_id' => null]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Map', ['album_id' => null]);
		$this->assertOk($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Map', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
	}
}