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

namespace Tests\Feature_v2\Search;

use Tests\Feature_v2\Base\BaseApiV2Test;

class SearchTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJson('Search::init');
		$this->assertUnprocessable($response);
		$response->assertSee('The album id field must be present.');

		$response = $this->getJsonWithData('Search::init', ['album_id' => null]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search::init', ['album_id' => null]);
		$this->assertOk($response);
		$response->assertJson(['search_minimum_length' => 4]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', ['album_id' => null]);
		$this->assertUnprocessable($response);
		$response->assertSee('The terms field is required.');

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', ['album_id' => null, 'terms' => base64_encode('something')]);
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [],
			'photos' => [],
			'current_page' => 1,
			'from' => 0,
			'last_page' => 1,
			'per_page' => 1000,
			'to' => 0,
			'total' => 0,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', ['album_id' => null, 'terms' => base64_encode($this->album1->title)]);
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [
				['id' => $this->album1->id],
			],
			'photos' => [],
			'current_page' => 1,
			'from' => 0,
			'last_page' => 1,
			'per_page' => 1000,
			'to' => 0,
			'total' => 0,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Search', ['album_id' => $this->album1->id, 'terms' => base64_encode('something')]);
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [],
			'photos' => [],
			'current_page' => 1,
			'from' => 0,
			'last_page' => 1,
			'per_page' => 1000,
			'to' => 0,
			'total' => 0,
		]);
	}
}