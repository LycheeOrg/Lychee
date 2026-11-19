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

namespace Tests\Feature_v3\Album;

use App\Enum\SmartAlbumType;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 106 (FR-106-02, S-106-01 … 05): members of the On This Day smart
 * album on 1 November 2026.
 */
class OnThisDayAlbumTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->travelTo(new \DateTimeImmutable('2026-11-01 12:00:00'));
		// The fixture photos are taken now: move them to another day.
		DB::table('photos')->update(['taken_at' => '2020-05-01 12:00:00', 'taken_key' => '2020-05-01-12', 'month_day_key' => 501]);
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	private function photo(array $attributes): Photo
	{
		return Photo::factory()->owned_by($this->admin)->in($this->album1)->create($attributes);
	}

	/**
	 * @return string[]
	 */
	private function members(): array
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Album::photos', ['album_id' => SmartAlbumType::ON_THIS_DAY->value]);
		$this->assertOk($response);

		return $response->json('photos.*.id');
	}

	public function testTakenOnTodaysDateInAnEarlierYearIsListed(): void
	{
		$photo = $this->photo(['taken_at' => Carbon::create(2019, 11, 1, 15, 0, 0, 'UTC')]);

		self::assertSame([$photo->id], $this->members());
	}

	public function testTakenOnAnotherDayIsNotListed(): void
	{
		$this->photo(['taken_at' => Carbon::create(2019, 11, 2, 15, 0, 0, 'UTC')]);

		self::assertSame([], $this->members());
	}

	public function testUploadedOnTodaysDateWithoutTakenDateIsListedFromAnEarlierYearOnly(): void
	{
		$earlier = $this->photo(['taken_at' => null, 'initial_taken_at' => null, 'created_at' => '2024-11-01 09:00:00']);
		$this->photo(['taken_at' => null, 'initial_taken_at' => null, 'created_at' => '2026-11-01 09:00:00']);

		self::assertSame([$earlier->id], $this->members());
	}

	public function testTakenDateWinsOverUploadDate(): void
	{
		$this->photo(['taken_at' => Carbon::create(2019, 5, 5, 15, 0, 0, 'UTC'), 'created_at' => '2024-11-01 09:00:00']);

		self::assertSame([], $this->members());
	}

	public function testTakenAfterMidnightInItsOwnZoneIsListedOnThatDay(): void
	{
		// 1 November 00:30 in Paris, stored as 31 October 23:30 UTC.
		$photo = $this->photo(['taken_at' => Carbon::create(2019, 11, 1, 0, 30, 0, 'Europe/Paris'), 'taken_at_orig_tz' => 'Europe/Paris']);

		self::assertSame([$photo->id], $this->members());
	}
}
