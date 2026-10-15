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
use Tests\AbstractTestCase;

/**
 * Covers {@see PhotoBucketComputer}: the granularity-free
 * `photo_album.bucket_key` per sort column (FR-092-03), `OWNER_ID`
 * excluded, and granularity resolution.
 */
class PhotoBucketComputerTest extends AbstractTestCase
{
	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	private function computer(?TitleBucketMode $mode = null, ?TimelinePhotoGranularity $default = null): PhotoBucketComputer
	{
		$config_manager = \Mockery::mock(ConfigManager::class);
		if ($mode !== null) {
			$config_manager->shouldReceive('getValueAsEnum')->with('photo_title_bucket_mode', TitleBucketMode::class)->andReturn($mode);
		}
		if ($default !== null) {
			$config_manager->shouldReceive('getValueAsEnum')->with('timeline_photos_granularity', TimelinePhotoGranularity::class)->andReturn($default);
		}

		return new PhotoBucketComputer($config_manager);
	}

	/**
	 * @param array<string,mixed> $overrides
	 */
	private function row(array $overrides = []): object
	{
		return (object) array_merge([
			'taken_key' => '2026-07-01-16',
			'created_at' => '2026-07-02 03:04:05.000000',
			'title_date_key' => '2024-03-01',
			'title_base' => 'Trip',
			'is_highlighted' => 0,
			'type' => 'image/jpeg',
			'rating_avg' => null,
		], $overrides);
	}

	public function testOwnerIdIsNotBucketableAndReadsNoConfig(): void
	{
		// No expectation set: any config call would throw.
		$this->assertNull($this->computer()->keyForRow(ColumnSortingType::OWNER_ID, $this->row()));
	}

	public function testTakenAtIsTheCaptureLocalKey(): void
	{
		$computer = $this->computer();
		$this->assertSame('2026-07-01-16', $computer->keyForRow(ColumnSortingType::TAKEN_AT, $this->row()));
		$this->assertNull($computer->keyForRow(ColumnSortingType::TAKEN_AT, $this->row(['taken_key' => null])));
	}

	public function testCreatedAtIsTheStoredValueToTheHour(): void
	{
		$this->assertSame('2026-07-02-03', $this->computer()->keyForRow(ColumnSortingType::CREATED_AT, $this->row()));
	}

	public function testTitleDatePrefixIsTheTitleDateKey(): void
	{
		$computer = $this->computer(TitleBucketMode::DATE_PREFIX);
		$this->assertSame('2024-03-01', $computer->keyForRow(ColumnSortingType::TITLE, $this->row()));
		$this->assertNull($computer->keyForRow(ColumnSortingType::TITLE, $this->row(['title_date_key' => null])));
	}

	public function testTitleAlphabeticalIsTheTitleBaseCappedAt255(): void
	{
		$computer = $this->computer(TitleBucketMode::ALPHABETICAL);
		$this->assertSame('Trip', $computer->keyForRow(ColumnSortingType::TITLE, $this->row()));

		$long = str_repeat('é', 300);
		$this->assertSame(str_repeat('é', 255), $computer->keyForRow(ColumnSortingType::TITLE, $this->row(['title_base' => $long])));
		$this->assertSame('', $computer->keyForRow(ColumnSortingType::TITLE, $this->row(['title_base' => null])));
	}

	public function testHighlightedIsOneOrZero(): void
	{
		$computer = $this->computer();
		$this->assertSame('1', $computer->keyForRow(ColumnSortingType::IS_HIGHLIGHTED, $this->row(['is_highlighted' => 1])));
		$this->assertSame('0', $computer->keyForRow(ColumnSortingType::IS_HIGHLIGHTED, $this->row(['is_highlighted' => false])));
	}

	public function testTypeIsTheValue(): void
	{
		$this->assertSame('video/mp4', $this->computer()->keyForRow(ColumnSortingType::TYPE, $this->row(['type' => 'video/mp4'])));
	}

	public function testRatingIsTheRoundedAverage(): void
	{
		$computer = $this->computer();
		$this->assertSame('5', $computer->keyForRow(ColumnSortingType::RATING_AVG, $this->row(['rating_avg' => '4.5000'])));
		$this->assertSame('4', $computer->keyForRow(ColumnSortingType::RATING_AVG, $this->row(['rating_avg' => '4.4999'])));
		$this->assertNull($computer->keyForRow(ColumnSortingType::RATING_AVG, $this->row()));
	}

	public function testResolveGranularityPrefersTheAlbumOverride(): void
	{
		$computer = $this->computer(default: TimelinePhotoGranularity::MONTH);
		$this->assertSame(TimelinePhotoGranularity::HOUR, $computer->resolveGranularity(TimelinePhotoGranularity::HOUR));
		$this->assertSame(TimelinePhotoGranularity::MONTH, $computer->resolveGranularity(null));
		$this->assertSame(TimelinePhotoGranularity::MONTH, $computer->resolveGranularity(TimelinePhotoGranularity::DEFAULT));
		$this->assertSame(TimelinePhotoGranularity::MONTH, $computer->resolveGranularity(TimelinePhotoGranularity::DISABLED));
	}
}
