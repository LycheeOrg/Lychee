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

use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers the timeline of notable moments of `GET /api/v3/Insights`
 * (Feature 085, FR-085-19, S-085-26).
 */
class InsightsTimelineTest extends BaseApiWithDataTest
{
	use InsightsDataset;

	/** @var array<int,array{kind:string,category:string,date:string,subject:string|null,value:float|int|null,photo_id:string|null}> */
	private array $events;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->seedInsightsDataset();

		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'library']);
		$this->assertOk($response);
		$this->events = $response->json('timeline');
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testFirstsAndLasts(): void
	{
		$this->assertEvent('first_capture', 'first_last', '2024-06-15', $this->p5->id);
		$this->assertEvent('last_capture', 'first_last', '2025-03-10', $this->p4->id);
		$this->assertEvent('first_video', 'first_last', '2025-01-02', $this->p3->id);
		$this->assertEvent('first_located', 'first_last', '2025-01-01', $this->p1->id);
		$this->assertEvent('first_with_people', 'first_last', '2025-01-01', $this->p1->id);
		$this->assertEvent('first_highlighted', 'first_last', '2025-01-01', $this->p1->id);
	}

	public function testFirstAndLastPhotoPerDevice(): void
	{
		$this->assertEvent('device_first', 'device', '2025-01-01', $this->p1->id, 'Nikon D850');
		$this->assertEvent('device_last', 'device', '2025-01-02', $this->p2->id, 'Nikon D850');
		$this->assertEvent('device_first', 'device', '2025-01-02', $this->p3->id, 'Apple iPhone 13 Pro');
		$this->assertEvent('device_last', 'device', '2025-03-10', $this->p4->id, 'Apple iPhone 13 Pro');
		// No event for unknown devices.
		self::assertCount(4, array_filter($this->events, fn (array $e) => $e['category'] === 'device'));
	}

	public function testRecordsOfDatedItems(): void
	{
		$this->assertEvent('longest_video', 'record', '2025-01-02', $this->p3->id, null, 20.4);
		$this->assertEvent('largest_file', 'record', '2025-01-02', $this->p3->id, null, 10000);
		// The undated ISO 3200 photo has no date and gives no event.
		$this->assertEvent('highest_iso', 'record', '2025-01-02', $this->p2->id, null, 400);
		$this->assertEvent('longest_exposure', 'record', '2025-01-02', $this->p2->id, null, 1 / 60);
		$this->assertEvent('widest_aperture', 'record', '2025-03-10', $this->p4->id, null, 1.5);
		$this->assertEvent('longest_focal', 'record', '2025-01-02', $this->p2->id, null, 70);
	}

	public function testBreakAndStreakBounds(): void
	{
		$this->assertEvent('break_start', 'break', '2024-06-15');
		$this->assertEvent('break_end', 'break', '2025-01-01');
		$this->assertEvent('streak_start', 'break', '2025-01-01');
		$this->assertEvent('streak_end', 'break', '2025-01-02');
	}

	public function testNoMilestoneBelowOneHundred(): void
	{
		self::assertSame([], array_values(array_filter($this->events, fn (array $e) => $e['category'] === 'milestone')));
	}

	public function testEventsAreInDateOrder(): void
	{
		$dates = array_column($this->events, 'date');
		$sorted = $dates;
		sort($sorted);
		self::assertSame($sorted, $dates);
	}

	private function assertEvent(string $kind, string $category, string $date, ?string $photo_id = null, ?string $subject = null, int|float|null $value = null): void
	{
		$matches = array_values(array_filter($this->events, fn (array $e) => $e['kind'] === $kind && ($subject === null || $e['subject'] === $subject)));
		self::assertCount(1, $matches, $kind . ' ' . ($subject ?? ''));
		self::assertSame($category, $matches[0]['category']);
		self::assertSame($date, $matches[0]['date']);
		if ($photo_id !== null) {
			self::assertSame($photo_id, $matches[0]['photo_id']);
		}
		if ($value !== null) {
			self::assertEqualsWithDelta($value, $matches[0]['value'], 0.0001);
		}
	}
}
