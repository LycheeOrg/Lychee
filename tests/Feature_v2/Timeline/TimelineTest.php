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

namespace Tests\Feature_v2\Timeline;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiV2Test;

class TimelineTest extends BaseApiV2Test
{
	public function testUnauthorized(): void
	{
		$response = $this->getJson('Timeline');
		$this->assertUnauthorized($response);

		$response = $this->getJson('Timeline::dates');
		$this->assertUnauthorized($response);

		$response = $this->getJson('Timeline::init');
		$this->assertOk($response);
		$response->assertJson([
			'photo_layout' => 'square',
			'is_timeline_page_enabled' => true,
			'config' => [],
			'rights' => [],
		]);
	}

	public function testAuthorizedPublic(): void
	{
		Configs::set('timeline_photos_public', '1');
		Configs::invalidateCache();
		$response = $this->getJson('Timeline');
		$this->assertOk($response);

		$response = $this->getJson('Timeline::dates');
		$this->assertOk($response);

		$response = $this->getJson('Timeline::dates', ['date' => '2021-01-01']);
		$this->assertOk($response);

		$response = $this->getJson('Timeline::dates', ['photoId' => $this->photo1->id]);
		$this->assertOk($response);

		$response = $this->getJson('Timeline::init');
		$this->assertOk($response);
		$response->assertJson([
			'photo_layout' => 'square',
			'is_timeline_page_enabled' => true,
			'config' => [],
			'rights' => [],
		]);

		Configs::set('timeline_photos_public', '0');
		Configs::invalidateCache();
	}
}