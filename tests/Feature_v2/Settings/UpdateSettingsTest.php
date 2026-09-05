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

namespace Tests\Feature_v2\Settings;

use App\Events\AlbumListingCacheFlushRequested;
use App\Jobs\RecomputeRootAlbumBucketsJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class UpdateSettingsTest extends BaseApiWithDataTest
{
	// ── Feature 062 (FR-062-07, G6, S-062-10..12): root bucket recompute ──

	/** @return array<int,array<string,string>> */
	public static function rootBucketRecomputeConfigsProvider(): array
	{
		return [
			'sorting_albums_col' => [['key' => 'sorting_albums_col', 'value' => 'created_at']],
			'sorting_albums_order' => [['key' => 'sorting_albums_order', 'value' => 'ASC']],
			'title_bucket_mode' => [['key' => 'title_bucket_mode', 'value' => 'date_prefix']],
			'title_bucket_prefix_length' => [['key' => 'title_bucket_prefix_length', 'value' => '1']],
		];
	}

	/**
	 * @param array<string,string> $config
	 */
	#[DataProvider('rootBucketRecomputeConfigsProvider')]
	public function testChangingRootBucketRecomputeConfigDispatchesJob(array $config): void
	{
		Queue::fake([RecomputeRootAlbumBucketsJob::class]);

		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [$config],
		]);
		$this->assertOk($response);

		Queue::assertPushed(RecomputeRootAlbumBucketsJob::class);
	}

	/**
	 * `timeline_albums_granularity` is a level-1 (Supporter Edition) config —
	 * kept as its own test rather than folded into the data provider above,
	 * which runs unconditionally.
	 */
	public function testChangingTimelineGranularityConfigDispatchesJob(): void
	{
		$this->requireSe();
		Queue::fake([RecomputeRootAlbumBucketsJob::class]);

		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'timeline_albums_granularity',
					'value' => 'year',
				],
			],
		]);
		$this->assertOk($response);

		Queue::assertPushed(RecomputeRootAlbumBucketsJob::class);
	}

	public function testChangingUnrelatedConfigDoesNotDispatchRootBucketRecompute(): void
	{
		Queue::fake([RecomputeRootAlbumBucketsJob::class]);

		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertOk($response);

		Queue::assertNotPushed(RecomputeRootAlbumBucketsJob::class);
	}

	public function testChangingAlbumSortingConfigDispatchesCoarseFlush(): void
	{
		Event::fake([AlbumListingCacheFlushRequested::class]);

		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'sorting_albums_col',
					'value' => 'created_at',
				],
			],
		]);
		$this->assertOk($response);

		Event::assertDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testChangingUnrelatedConfigDoesNotDispatchCoarseFlush(): void
	{
		Event::fake([AlbumListingCacheFlushRequested::class]);

		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertOk($response);

		Event::assertNotDispatched(AlbumListingCacheFlushRequested::class);
	}

	public function testUpdateSettingsGuest(): void
	{
		$response = $this->postJson('Settings::setConfigs', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'key',
					'value' => 'value',
				],
			],
		]);
		$this->assertUnprocessable($response);
		$response->assertSee('is not a valid configuration key');
		$response->assertDontSee('is not a valid configuration value');

		$response = $this->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => 'value',
				],
			],
		]);
		$this->assertUnprocessable($response);
		$response->assertDontSee('is not a valid configuration key');
		$response->assertSee('is not a valid configuration value');

		$response = $this->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertUnauthorized($response);
	}

	public function testUpdateSettingUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateSettingsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [
				[
					'key' => 'version',
					'value' => '1',
				],
			],
		]);
		$this->assertOk($response);
	}

	public function testUpdateCssForbidden(): void
	{
		$response = $this->postJson('Settings::setCSS', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Settings::setCSS', [
			'css' => 'body { background-color: red; }',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setCSS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setCSS', [
			'css' => 'body { background-color: red; }',
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateCssAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setCSS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->postJson('Settings::setCSS', [
			'css' => 'body { background-color: red; }',
		]);
		$this->assertNoContent($response);

		// reset
		$response = $this->actingAs($this->admin)->postJson('Settings::setCSS', [
			'css' => '',
		]);
		$this->assertNoContent($response);
	}

	public function testupdateJsForbiddne(): void
	{
		$response = $this->postJson('Settings::setJS', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Settings::setJS', [
			'js' => 'console.log("Hello World!");',
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setJS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setJS', [
			'js' => 'console.log("Hello World!");',
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateJsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Settings::setJS', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->admin)->postJson('Settings::setJS', [
			'js' => 'console.log("Hello World!");',
		]);
		$this->assertNoContent($response);

		// reset
		$response = $this->actingAs($this->admin)->postJson('Settings::setJS', [
			'js' => '',
		]);
		$this->assertNoContent($response);
	}
}