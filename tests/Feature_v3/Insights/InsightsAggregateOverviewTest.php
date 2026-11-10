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

namespace Tests\Feature_v3\Insights;

use App\Models\Photo;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers the overview, storage, people, places and time-span figures of
 * `GET /api/v3/Insights` (Feature 085, FR-085-06 … FR-085-09, FR-085-14,
 * S-085-01, S-085-04 … S-085-06, S-085-08, S-085-11, S-085-13, S-085-16,
 * S-085-19).
 */
class InsightsAggregateOverviewTest extends BaseApiWithDataTest
{
	use InsightsDataset;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->seedInsightsDataset();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testLibraryOverviewAndStorage(): void
	{
		$response = $this->insights(['period' => 'library']);

		$response->assertJsonPath('years', [2024, 2025]);
		$response->assertJsonPath('overview', [
			'total' => 6,
			'photos' => 4,
			'videos' => 1,
			'others' => 1,
			'highlighted' => 1,
			'albums' => 3,
			'photos_without_album' => 2,
		]);
		$response->assertJsonPath('storage', [
			'total_size' => 16500,
			'size_unknown' => 1,
			'average_photo_size' => 2000,
			'average_video_size' => 10000,
		]);
	}

	public function testLibraryPeopleAndPlaces(): void
	{
		$response = $this->insights(['period' => 'library']);

		$response->assertJsonPath('people.has_faces', true);
		$response->assertJsonPath('people.photos_with_people', 2);
		$response->assertJsonPath('people.people', 2);
		$response->assertJsonPath('people.faces', 3);
		self::assertEqualsWithDelta(1.5, $response->json('people.faces_per_photo'), 0.0001);
		$response->assertJsonPath('places.located', 2);
		self::assertEqualsWithDelta(2 / 6, $response->json('places.share'), 0.0001);
	}

	public function testLibraryTimeSpan(): void
	{
		$response = $this->insights(['period' => 'library']);

		$response->assertJsonPath('time_span.first.photo_id', $this->p5->id);
		$response->assertJsonPath('time_span.first.taken_at', '2024-06-15 09:00');
		self::assertNotNull($response->json('time_span.first.thumb_url'));
		$response->assertJsonPath('time_span.last.photo_id', $this->p4->id);
		$response->assertJsonPath('time_span.last.taken_at', '2025-03-10 12:00');
		$response->assertJsonPath('time_span.busiest_day.date', '2025-01-02');
		$response->assertJsonPath('time_span.busiest_day.count', 2);
		self::assertContains($response->json('time_span.busiest_day.photo_id'), [$this->p2->id, $this->p3->id]);
		$response->assertJsonPath('time_span.days_with_photos', 4);
		$response->assertJsonPath('time_span.calendar_days', 269);
		$response->assertJsonPath('time_span.undated', 1);
		$response->assertJsonPath('time_span.longest_daily_streak', ['length' => 2, 'from' => '2025-01-01', 'to' => '2025-01-02']);
		$response->assertJsonPath('time_span.longest_weekly_streak', ['length' => 1, 'from' => '2024-06-10', 'to' => '2024-06-10']);
		$response->assertJsonPath('time_span.longest_break', ['length' => 199, 'from' => '2024-06-15', 'to' => '2025-01-01']);
	}

	public function testYearUsesLocalDates(): void
	{
		$response = $this->insights(['period' => 'year', 'year' => 2025]);

		$response->assertJsonPath('years', [2024, 2025]);
		$response->assertJsonPath('overview', [
			'total' => 4,
			'photos' => 3,
			'videos' => 1,
			'others' => 0,
			'highlighted' => 1,
			'albums' => 2,
			'photos_without_album' => 1,
		]);
		$response->assertJsonPath('people.people', 2);
		$response->assertJsonPath('time_span.first.photo_id', $this->p1->id);
		$response->assertJsonPath('time_span.undated', 0);
	}

	public function testPreviousYearLeavesOutThePhotoStoredOnItsLastDay(): void
	{
		$response = $this->insights(['period' => 'year', 'year' => 2024]);

		$response->assertJsonPath('overview.total', 1);
		$response->assertJsonPath('overview.others', 1);
		$response->assertJsonPath('overview.albums', 0);
		$response->assertJsonPath('people.photos_with_people', 0);
		$response->assertJsonPath('people.people', 0);
		$response->assertJsonPath('people.has_faces', true);
	}

	public function testRangeIsInclusive(): void
	{
		$response = $this->insights(['period' => 'range', 'from' => '2025-01-02', 'to' => '2025-03-10']);

		$response->assertJsonPath('overview.total', 3);
		$response->assertJsonPath('overview.albums', 2);
	}

	public function testOtherOwnersAreLeftOut(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Insights', ['period' => 'library']);

		$this->assertOk($response);
		$response->assertJsonPath('overview.total', Photo::query()->where('owner_id', '=', $this->userMayUpload1->id)->count());
		$response->assertJsonPath('people.has_faces', false);
	}

	public function testAdminReadsAnOwner(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'owner_id' => $this->photographer->id]);

		$this->assertOk($response);
		$response->assertJsonPath('overview.total', 6);
		$response->assertJsonPath('overview.albums', 3);
	}

	public function testAdminReadsTheWholeInstance(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'whole_instance' => 1]);

		$this->assertOk($response);
		$response->assertJsonPath('overview.total', Photo::query()->count());
		$response->assertJsonPath('people.people', 2);
	}

	public function testEmptyLibrary(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library']);

		$this->assertOk($response);
		$response->assertJsonPath('years', []);
		$response->assertJsonPath('overview.total', 0);
		$response->assertJsonPath('overview.albums', 1);
		$response->assertJsonPath('storage.average_photo_size', null);
		self::assertEqualsWithDelta(0.0, $response->json('places.share'), 0.0001);
		$response->assertJsonPath('people.has_faces', false);
		$response->assertJsonPath('people.faces_per_photo', null);
		$response->assertJsonPath('time_span.first', null);
		$response->assertJsonPath('time_span.busiest_day', null);
		$response->assertJsonPath('time_span.longest_break', null);
		$response->assertJsonPath('calendar.dates', []);
	}

	/**
	 * @param array<string,mixed> $data
	 */
	private function insights(array $data): TestResponse
	{
		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', $data);
		$this->assertOk($response);

		return $response;
	}
}
