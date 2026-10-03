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

namespace Tests\Feature_v3\Metrics;

use App\Enum\LiveMetricsAccess;
use App\Enum\LiveMetricsCleanup;
use App\Enum\MetricsAction;
use App\Models\Configs;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /api/v3/Metrics` (Feature 079, S-079-01..16).
 *
 * Events are inserted directly at fixed `created_at` values so that the
 * per-minute grouping and the ordering are deterministic.
 */
class LiveMetricsListTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
		$this->requireSe();
		Configs::set('live_metrics_enabled', true);
	}

	public function tearDown(): void
	{
		Configs::set('live_metrics_enabled', false);
		Configs::set('live_metrics_access', LiveMetricsAccess::ADMIN);
		Configs::set('live_metrics_result_limit', 1000);
		Configs::set('live_metrics_cleanup', LiveMetricsCleanup::DEFERRED);
		$this->resetSe();
		parent::tearDown();
	}

	public function testFlagOffReturns403(): void
	{
		config(['features.struct-of-array' => false]);
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Metrics'));
	}

	public function testNonAdminForbiddenWhenAccessIsAdmin(): void
	{
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJsonV3('Metrics'));
	}

	public function testLiveMetricsDisabledReturns403(): void
	{
		Configs::set('live_metrics_enabled', false);
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Metrics'));
	}

	public function testGuestReturns401(): void
	{
		Configs::set('live_metrics_access', LiveMetricsAccess::LOGGEDIN);
		$this->assertUnauthorized($this->getJsonV3('Metrics'));
	}

	public function testEmpty(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertExactJson([
			'created_ats' => [],
			'actions' => [],
			'album_ids' => [],
			'photo_ids' => [],
			'titles' => [],
			'thumb_photo_ids' => [],
			'counts' => [],
			'is_truncated' => false,
		]);
	}

	public function testAdminSeesAllExceptPhotoVisitsAndTagAlbums(): void
	{
		$this->seedMixedEvents();

		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('actions', ['favourite', 'shared', 'download', 'visit']);
		$response->assertJsonPath('album_ids', [$this->album2->id, $this->album4->id, $this->album1->id, $this->album1->id]);
		$response->assertJsonPath('photo_ids', [$this->photo2->id, null, $this->photo1->id, null]);
		$response->assertJsonPath('titles', [$this->photo2->title, $this->album4->title, $this->photo1->title, $this->album1->title]);
		$response->assertJsonPath('counts', [1, 1, 1, 1]);
		$response->assertJsonPath('is_truncated', false);
	}

	public function testNonAdminSeesOwnAlbumsAndOwnPhotos(): void
	{
		Configs::set('live_metrics_access', LiveMetricsAccess::LOGGEDIN);
		$this->seedMixedEvents();
		// photo1 (owned by userMayUpload1) downloaded from album2 (owned by userMayUpload2).
		$this->insertEvent(MetricsAction::DOWNLOAD, $this->album2->id, $this->photo1->id, $this->ago(60));

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('actions', ['download', 'download', 'visit']);
		$response->assertJsonPath('album_ids', [$this->album2->id, $this->album1->id, $this->album1->id]);

		$response = $this->actingAs($this->userMayUpload2)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('actions', ['favourite', 'download']);
		$response->assertJsonPath('photo_ids', [$this->photo2->id, $this->photo1->id]);

		$response = $this->actingAs($this->userNoUpload)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('actions', []);
	}

	public function testThumbPhotoIds(): void
	{
		$this->album4->cover_id = $this->photo4->id;
		$this->album4->save();
		$auto_cover = DB::table('albums')->where('id', '=', $this->album1->id)->value('auto_cover_id_max_privilege');
		$this->assertNotNull($auto_cover);

		$this->insertEvent(MetricsAction::DOWNLOAD, $this->album1->id, $this->photo1b->id, $this->ago(4 * 60));
		$this->insertEvent(MetricsAction::SHARED, $this->album4->id, null, $this->ago(3 * 60));
		$this->insertEvent(MetricsAction::VISIT, $this->album1->id, null, $this->ago(2 * 60));
		$this->insertEvent(MetricsAction::VISIT, $this->album5->id, null, $this->ago(60));

		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('thumb_photo_ids', [null, $auto_cover, $this->photo4->id, $this->photo1b->id]);
	}

	public function testTitleIsNotEscaped(): void
	{
		$this->album4->title = '<b>&</b>LM';
		$this->album4->save();
		$this->insertEvent(MetricsAction::VISIT, $this->album4->id, null, $this->ago(60));

		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('titles', ['<b>&</b>LM']);
	}

	public function testEventsAreGroupedPerMinute(): void
	{
		$minute = date('Y-m-d H:i', strtotime('-2 hours'));
		$next_minute = date('Y-m-d H:i', strtotime('-2 hours +1 minute'));
		$this->insertEvent(MetricsAction::DOWNLOAD, $this->album1->id, $this->photo1->id, $minute . ':05');
		$this->insertEvent(MetricsAction::DOWNLOAD, $this->album1->id, $this->photo1->id, $minute . ':40');
		$this->insertEvent(MetricsAction::DOWNLOAD, $this->album1->id, $this->photo1->id, $minute . ':20');
		$this->insertEvent(MetricsAction::DOWNLOAD, $this->album1->id, $this->photo1->id, $next_minute . ':00');
		// Same minute, other action: its own group.
		$this->insertEvent(MetricsAction::FAVOURITE, $this->album1->id, $this->photo1->id, $minute . ':30');

		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('actions', ['download', 'download', 'favourite']);
		$response->assertJsonPath('counts', [1, 3, 1]);
		$response->assertJsonPath('created_ats', [
			date('c', strtotime($next_minute . ':00')),
			date('c', strtotime($minute . ':40')),
			date('c', strtotime($minute . ':30')),
		]);
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $response->json('created_ats.0'));
	}

	public function testResultIsCapped(): void
	{
		Configs::set('live_metrics_result_limit', 2);
		$this->insertEvent(MetricsAction::VISIT, $this->album1->id, null, $this->ago(30));
		$this->insertEvent(MetricsAction::VISIT, $this->album2->id, null, $this->ago(20));
		$this->insertEvent(MetricsAction::VISIT, $this->album3->id, null, $this->ago(10));

		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('album_ids', [$this->album3->id, $this->album2->id]);
		$response->assertJsonPath('is_truncated', true);

		DB::table('live_metrics')->where('album_id', '=', $this->album1->id)->delete();

		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('album_ids', [$this->album3->id, $this->album2->id]);
		$response->assertJsonPath('is_truncated', false);
	}

	/**
	 * @return array<string,array{0:LiveMetricsCleanup,1:int}>
	 */
	public static function cleanupModes(): array
	{
		return [
			'deferred' => [LiveMetricsCleanup::DEFERRED, 1],
			'sync' => [LiveMetricsCleanup::SYNC, 1],
			'disabled' => [LiveMetricsCleanup::DISABLED, 2],
		];
	}

	#[DataProvider('cleanupModes')]
	public function testExpiredEventsAreHiddenAndCleanedUpPerMode(LiveMetricsCleanup $mode, int $rows_left): void
	{
		Configs::set('live_metrics_cleanup', $mode);
		$this->insertEvent(MetricsAction::VISIT, $this->album1->id, null, $this->ago(40 * 24 * 60));
		$this->insertEvent(MetricsAction::VISIT, $this->album2->id, null, $this->ago(60));

		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$this->assertOk($response);
		$response->assertJsonPath('album_ids', [$this->album2->id]);
		$this->assertDatabaseCount('live_metrics', $rows_left);
	}

	#[DataProvider('cleanupModes')]
	public function testV2HidesExpiredEventsAndHonoursCleanupMode(LiveMetricsCleanup $mode, int $rows_left): void
	{
		Configs::set('live_metrics_cleanup', $mode);
		$this->insertEvent(MetricsAction::VISIT, $this->album1->id, null, $this->ago(40 * 24 * 60));
		$this->insertEvent(MetricsAction::VISIT, $this->album2->id, null, $this->ago(60));

		$response = $this->actingAs($this->admin)->getJson('Metrics');
		$this->assertOk($response);
		$this->assertCount(1, $response->json());
		$this->assertEquals($this->album2->id, $response->json('0.album_id'));
		$this->assertDatabaseCount('live_metrics', $rows_left);
	}

	public function testListingRunsOneSelectOnLiveMetrics(): void
	{
		$this->seedMixedEvents();

		DB::enableQueryLog();
		$response = $this->actingAs($this->admin)->getJsonV3('Metrics');
		$queries = DB::getQueryLog();
		DB::disableQueryLog();

		$this->assertOk($response);
		$selects = array_filter(
			$queries,
			fn (array $q) => str_starts_with(strtolower(ltrim($q['query'])), 'select') && str_contains($q['query'], 'live_metrics')
		);
		$this->assertCount(1, $selects);
	}

	/**
	 * Admin view, newest first: favourite photo2, shared album4, download
	 * photo1, visit album1. The photo visit and the tag-album visit are
	 * never listed.
	 */
	private function seedMixedEvents(): void
	{
		$this->insertEvent(MetricsAction::VISIT, $this->album1->id, null, $this->ago(180));
		$this->insertEvent(MetricsAction::VISIT, $this->album1->id, $this->photo1->id, $this->ago(120));
		$this->insertEvent(MetricsAction::DOWNLOAD, $this->album1->id, $this->photo1->id, $this->ago(90));
		$this->insertEvent(MetricsAction::SHARED, $this->album4->id, null, $this->ago(30));
		$this->insertEvent(MetricsAction::VISIT, $this->tagAlbum1->id, null, $this->ago(10));
		$this->insertEvent(MetricsAction::FAVOURITE, $this->album2->id, $this->photo2->id, $this->ago(5));
	}

	private function insertEvent(MetricsAction $action, string $album_id, ?string $photo_id, string $created_at): void
	{
		DB::table('live_metrics')->insert([
			'created_at' => $created_at,
			'visitor_id' => 'visitor',
			'action' => $action->value,
			'album_id' => $album_id,
			'photo_id' => $photo_id,
		]);
	}

	private function ago(int $minutes): string
	{
		return date('Y-m-d H:i:s', strtotime('-' . $minutes . ' minutes'));
	}
}
