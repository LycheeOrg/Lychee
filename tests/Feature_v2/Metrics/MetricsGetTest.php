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
use App\Models\LiveMetrics;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class MetricsGetTest extends BaseApiWithDataTest
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
		$this->assertOk($response); // 1: - viewed album4
		$response = $this->get('gallery/' . $this->album4->id);
		$this->assertOk($response); // 2: - shared album4
		$response = $this->postJson('Metrics::photo', ['photo_ids' => [$this->photo4->id]]);
		$this->assertNoContent($response); // 3: viewed photo4
		$response = $this->postJson('Metrics::favourite', ['photo_ids' => [$this->photo4->id]]);
		$this->assertNoContent($response); // 4: favourite photo4
		$response = $this->get('gallery/' . $this->album4->id . '/' . $this->photo4->id);
		$this->assertOk($response); // 5: shared photo4

		$this->assertEquals(5, LiveMetrics::count());
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album', ['album_id' => $this->album4->id]);
		$this->assertEquals(5, LiveMetrics::count()); // Still 5: We do not count the logged in users yet.
		Configs::set('metrics_logged_in_users_enabed', true);
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Album', ['album_id' => $this->album4->id]);
		$this->assertEquals(6, LiveMetrics::count()); // 6: We do count the logged in users now.
		$response = $this->actingAs($this->admin)->getJsonWithData('Album', ['album_id' => $this->album4->id]);
		$this->assertEquals(6, LiveMetrics::count()); // Still 6: We do not count the admin user though.

		// Now we check/fetch the metrics data.
		$response = $this->actingAs($this->userMayUpload1)->getJson('Metrics');
		$this->assertOk($response);
		$this->assertCount(0, $response->json()); // Album 4 (which we visited above) belongs to userLocked, not userMayUpload1

		$response = $this->actingAs($this->userLocked)->getJson('Metrics');
		$this->assertOk($response);
		$this->assertCount(5, $response->json()); // while we have 6 events in the table, we do not show the view of the photo, those are too noisy.

		$response = $this->actingAs($this->admin)->getJson('Metrics');
		$this->assertOk($response);
		$this->assertCount(5, $response->json());
	}
}
