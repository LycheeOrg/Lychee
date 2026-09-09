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

namespace Tests\Unit\Services;

use App\Enum\ColumnSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Repositories\ConfigManager;
use App\Services\PhotoBucketComputer;
use Illuminate\Support\Carbon;
use Tests\AbstractTestCase;

/**
 * Covers {@see \App\Services\PhotoBucketComputer} (all 6 bucketable
 * `ColumnSortingPhotoType` branches, and `OWNER_ID` always excluded).
 */
class PhotoBucketComputerTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	private function makeComputer(): array
	{
		$config_manager = \Mockery::mock(ConfigManager::class);

		return [new PhotoBucketComputer($config_manager), $config_manager];
	}

	private function compute(
		PhotoBucketComputer $computer,
		ColumnSortingType $sorting_column,
		TimelinePhotoGranularity $granularity = TimelinePhotoGranularity::YEAR,
		string $title = '',
		string $title_base = '',
		?Carbon $created_at = null,
		?Carbon $taken_at = null,
		bool $is_highlighted = false,
		string $type = 'image/jpeg',
		?string $rating_avg = null,
	): ?string {
		return $computer->compute(
			sorting_column: $sorting_column,
			granularity: $granularity,
			title: $title,
			title_base: $title_base,
			created_at: $created_at ?? Carbon::create(2024, 3, 15, 10, 0, 0),
			taken_at: $taken_at,
			is_highlighted: $is_highlighted,
			type: $type,
			rating_avg: $rating_avg,
		);
	}

	// ── OWNER_ID ───────────────────────────────────────────────────

	public function testOwnerIdAlwaysReturnsNullWithoutTouchingConfig(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		// No shouldReceive() set up at all: any config call would throw.

		$result = $this->compute($computer, ColumnSortingType::OWNER_ID, rating_avg: '4.5000');

		$this->assertNull($result);
	}

	// ── CREATED_AT / TAKEN_AT ───────────────────────────────────────

	public function testCreatedAtBucketsByYear(): void
	{
		[$computer] = $this->makeComputer();
		$result = $this->compute($computer, ColumnSortingType::CREATED_AT, TimelinePhotoGranularity::YEAR, created_at: Carbon::create(2024, 3, 15));
		$this->assertSame('2024', $result);
	}

	public function testCreatedAtBucketsByMonth(): void
	{
		[$computer] = $this->makeComputer();
		$result = $this->compute($computer, ColumnSortingType::CREATED_AT, TimelinePhotoGranularity::MONTH, created_at: Carbon::create(2024, 3, 15));
		$this->assertSame('2024-03', $result);
	}

	public function testCreatedAtBucketsByDay(): void
	{
		[$computer] = $this->makeComputer();
		$result = $this->compute($computer, ColumnSortingType::CREATED_AT, TimelinePhotoGranularity::DAY, created_at: Carbon::create(2024, 3, 15));
		$this->assertSame('2024-03-15', $result);
	}

	public function testCreatedAtBucketsByHour(): void
	{
		[$computer] = $this->makeComputer();
		$result = $this->compute($computer, ColumnSortingType::CREATED_AT, TimelinePhotoGranularity::HOUR, created_at: Carbon::create(2024, 3, 15, 14));
		$this->assertSame('2024-03-15-14', $result);
	}

	public function testTakenAtBucketsByMonth(): void
	{
		[$computer] = $this->makeComputer();
		$result = $this->compute($computer, ColumnSortingType::TAKEN_AT, TimelinePhotoGranularity::MONTH, taken_at: Carbon::create(2023, 7, 4));
		$this->assertSame('2023-07', $result);
	}

	public function testTakenAtNullYieldsNullUnknownBucket(): void
	{
		[$computer] = $this->makeComputer();
		$result = $this->compute($computer, ColumnSortingType::TAKEN_AT, taken_at: null);
		$this->assertNull($result);
	}

	// ── TITLE (photo-specific config pair) ───────────────────────────

	public function testTitleAlphabeticalModeUsesPhotoSpecificPrefixLength(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		$config_manager->shouldReceive('getValueAsEnum')
			->once()
			->with('photo_title_bucket_mode', TitleBucketMode::class)
			->andReturn(TitleBucketMode::ALPHABETICAL);
		$config_manager->shouldReceive('getValueAsInt')
			->once()
			->with('photo_title_bucket_prefix_length')
			->andReturn(3);

		$result = $this->compute($computer, ColumnSortingType::TITLE, title: 'Vacation Photos', title_base: 'Vacation Photos');

		$this->assertSame('Vac', $result);
	}

	public function testTitleDateParsedModeParsesLeadingDate(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		$config_manager->shouldReceive('getValueAsEnum')
			->once()
			->with('photo_title_bucket_mode', TitleBucketMode::class)
			->andReturn(TitleBucketMode::DATE_PREFIX);

		$result = $this->compute($computer, ColumnSortingType::TITLE, TimelinePhotoGranularity::MONTH, title: '2020-03 Vacation', title_base: '2020-03 Vacation');

		$this->assertSame('2020-03', $result);
	}

	public function testTitleDateParsedModeUnparseableTitleYieldsNull(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		$config_manager->shouldReceive('getValueAsEnum')
			->once()
			->with('photo_title_bucket_mode', TitleBucketMode::class)
			->andReturn(TitleBucketMode::DATE_PREFIX);

		$result = $this->compute($computer, ColumnSortingType::TITLE, title: 'No date here', title_base: 'No date here');

		$this->assertNull($result);
	}

	public function testTitleBranchNeverReadsTheAlbumOnlyConfigKeys(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		// Mockery::mock() without shouldReceive() throws on any unexpected
		// call - if the TITLE branch read 'title_bucket_mode' (album-only)
		// instead of 'photo_title_bucket_mode', this would fail loudly.
		$config_manager->shouldReceive('getValueAsEnum')
			->once()
			->with('photo_title_bucket_mode', TitleBucketMode::class)
			->andReturn(TitleBucketMode::ALPHABETICAL);
		$config_manager->shouldReceive('getValueAsInt')
			->once()
			->with('photo_title_bucket_prefix_length')
			->andReturn(1);

		$this->compute($computer, ColumnSortingType::TITLE, title: 'Zebra', title_base: 'Zebra');

		// Mockery will flag any call to 'title_bucket_mode'/'title_bucket_prefix_length'
		// as an unexpected invocation at Mockery::close() time (tearDown()).
		$this->assertTrue(true);
	}

	// ── IS_HIGHLIGHTED ────────────────────────────────────────────

	public function testIsHighlightedTrueBucketsAsOne(): void
	{
		[$computer] = $this->makeComputer();
		$this->assertSame('1', $this->compute($computer, ColumnSortingType::IS_HIGHLIGHTED, is_highlighted: true));
	}

	public function testIsHighlightedFalseBucketsAsZero(): void
	{
		[$computer] = $this->makeComputer();
		$this->assertSame('0', $this->compute($computer, ColumnSortingType::IS_HIGHLIGHTED, is_highlighted: false));
	}

	// ── TYPE ──────────────────────────────────────────────────────

	public function testTypeBucketsByRawEnumValue(): void
	{
		[$computer] = $this->makeComputer();
		$this->assertSame('video/mp4', $this->compute($computer, ColumnSortingType::TYPE, type: 'video/mp4'));
	}

	// ── RATING_AVG ────────────────────────────────────────────────

	public function testRatingAvgRoundsToNearestIntegerString(): void
	{
		[$computer] = $this->makeComputer();
		$this->assertSame('5', $this->compute($computer, ColumnSortingType::RATING_AVG, rating_avg: '4.6000'));
		$this->assertSame('1', $this->compute($computer, ColumnSortingType::RATING_AVG, rating_avg: '1.4000'));
	}

	public function testRatingAvgNullYieldsNullUnknownBucket(): void
	{
		[$computer] = $this->makeComputer();
		$this->assertNull($this->compute($computer, ColumnSortingType::RATING_AVG, rating_avg: null));
	}

	// ── resolveGranularity() ──────────────────────────────────────

	public function testResolveGranularityPassesThroughExplicitValue(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		// The instance-wide default is always resolved (mirrors
		// AlbumBucketComputer::resolveGranularity()) even though it is
		// discarded here in favour of the explicit $candidate.
		$config_manager->shouldReceive('getValueAsEnum')
			->once()
			->with('timeline_photos_granularity', TimelinePhotoGranularity::class)
			->andReturn(TimelinePhotoGranularity::YEAR);

		$this->assertSame(TimelinePhotoGranularity::DAY, $computer->resolveGranularity(TimelinePhotoGranularity::DAY));
	}

	public function testResolveGranularityFallsBackToInstanceDefaultForNull(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		$config_manager->shouldReceive('getValueAsEnum')
			->once()
			->with('timeline_photos_granularity', TimelinePhotoGranularity::class)
			->andReturn(TimelinePhotoGranularity::MONTH);

		$this->assertSame(TimelinePhotoGranularity::MONTH, $computer->resolveGranularity(null));
	}

	public function testResolveGranularityFallsBackToInstanceDefaultForDefaultOrDisabled(): void
	{
		[$computer, $config_manager] = $this->makeComputer();
		$config_manager->shouldReceive('getValueAsEnum')
			->twice()
			->with('timeline_photos_granularity', TimelinePhotoGranularity::class)
			->andReturn(TimelinePhotoGranularity::DAY);

		$this->assertSame(TimelinePhotoGranularity::DAY, $computer->resolveGranularity(TimelinePhotoGranularity::DEFAULT));
		$this->assertSame(TimelinePhotoGranularity::DAY, $computer->resolveGranularity(TimelinePhotoGranularity::DISABLED));
	}
}
