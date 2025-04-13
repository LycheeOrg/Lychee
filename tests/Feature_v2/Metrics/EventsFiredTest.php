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

namespace Tests\Feature_v2\Metrics;

use App\Models\Configs;
use App\Models\Statistics;
use Tests\Feature_v2\Base\BaseApiV2Test;

class EventsFiredTest extends BaseApiV2Test
{
	public function setUp(): void
	{
		parent::setUp();
		Configs::set('metrics_enabled', true);
		Configs::invalidateCache();
	}

	public function tearDown(): void
	{
		Configs::set('metrics_enabled', false);
		Configs::invalidateCache();
		parent::tearDown();
	}

	public function testVisitSharedAlbum(): void
	{
		$response = $this->getJsonWithData('Album', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$this->assertEquals(1, Statistics::where('album_id', $this->album4->id)->firstOrFail()->visit_count);

		$response = $this->get('gallery/' . $this->album4->id);
		$this->assertOk($response);
		$this->assertEquals(1, Statistics::where('album_id', $this->album4->id)->firstOrFail()->shared_count);
	}

	public function testVisitSharedPhoto(): void
	{
		$response = $this->postJson('Metrics::photo', ['photo_ids' => [$this->photo4->id]]);
		$this->assertNoContent($response);
		$this->assertEquals(1, Statistics::where('photo_id', $this->photo4->id)->firstOrFail()->visit_count);

		$response = $this->postJson('Metrics::favourite', ['photo_ids' => [$this->photo4->id]]);
		$this->assertNoContent($response);
		$this->assertEquals(1, Statistics::where('photo_id', $this->photo4->id)->firstOrFail()->favourite_count);

		$response = $this->get('gallery/' . $this->album4->id . '/' . $this->photo4->id);
		$this->assertOk($response);
		$this->assertEquals(1, Statistics::where('photo_id', $this->photo4->id)->firstOrFail()->shared_count);
		$this->assertEquals(0, Statistics::where('album_id', $this->album4->id)->firstOrFail()->shared_count);
	}
}
