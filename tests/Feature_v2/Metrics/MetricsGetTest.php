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

use App\Enum\LiveMetricsAccess;
use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiV2Test;

class MetricsGetTest extends BaseApiV2Test
{
	public function tearDown(): void
	{
		Configs::set('live_metrics_enabled', false);
		parent::tearDown();
	}

	public function testGetMetricsDenied(): void
	{
		$response = $this->getJson('Metrics');
		$this->assertSupporterRequired($response);

		$this->requireSe();

		$response = $this->getJson('Metrics');
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Metrics');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->admin)->getJson('Metrics');
		$this->assertForbidden($response); // Not active
	}

	public function testGetMetricsOk(): void
	{
		$this->requireSe();
		Configs::set('live_metrics_enabled', true);
		$response = $this->actingAs($this->userMayUpload1)->getJson('Metrics');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->admin)->getJson('Metrics');
		$this->assertOk($response);

		Configs::set('live_metrics_access', LiveMetricsAccess::LOGGEDIN);
		$response = $this->actingAs($this->userMayUpload1)->getJson('Metrics');
		$this->assertOk($response);
	}

	public function testWithData(): void
	{
		$this->requireSe();
		Configs::set('live_metrics_enabled', true);
		Configs::set('live_metrics_access', LiveMetricsAccess::LOGGEDIN);

		$response = $this->getJsonWithData('Album', ['album_id' => $this->album4->id]);
		$this->assertOk($response);
		$response = $this->get('gallery/' . $this->album4->id);
		$this->assertOk($response);
		$response = $this->postJson('Metrics::photo', ['photo_ids' => [$this->photo4->id]]);
		$this->assertNoContent($response);
		$response = $this->postJson('Metrics::favourite', ['photo_ids' => [$this->photo4->id]]);
		$this->assertNoContent($response);
		$response = $this->get('gallery/' . $this->album4->id . '/' . $this->photo4->id);
		$this->assertOk($response);

		// userLocked
		$response = $this->actingAs($this->userMayUpload1)->getJson('Metrics');
		$this->assertOk($response);
		$this->assertCount(0, $response->json()); // Album 4 (which we visited above) belongs to userLocked, not userMayUpload1

		$response = $this->actingAs($this->userLocked)->getJson('Metrics');
		$this->assertOk($response);
		$this->assertCount(4, $response->json());

		$response = $this->actingAs($this->admin)->getJson('Metrics');
		$this->assertOk($response);
		$this->assertCount(4, $response->json());
	}
}
