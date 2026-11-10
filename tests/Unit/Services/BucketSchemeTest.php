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
use App\Enum\OrderSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Repositories\ConfigManager;
use App\Services\BucketScheme;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

/**
 * Covers {@see BucketScheme}: bucket ids derived at read time from a stored
 * key or a photo column (FR-092-05 … FR-092-08).
 */
class BucketSchemeTest extends AbstractTestCase
{
	private const PHOTO_CONFIGS = [
		'photo_title_bucket_mode' => TitleBucketMode::DATE_PREFIX,
		'photo_title_bucket_prefix_length' => 1,
		'timeline_photo_date_format_year' => 'Y',
		'timeline_photo_date_format_month' => 'M Y',
		'timeline_photo_date_format_day' => 'j M Y',
		'timeline_photo_date_format_hour' => 'g:i',
		'timeline_photo_date_format_hour_multi_day' => 'j M Y H:00',
		'title_bucket_mode' => TitleBucketMode::DATE_PREFIX,
		'title_bucket_prefix_length' => 1,
		'timeline_album_date_format_year' => 'Y',
		'timeline_album_date_format_month' => 'M Y',
		'timeline_album_date_format_day' => 'j M',
	];

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	/**
	 * @param array<string,mixed> $overrides
	 */
	private function config(array $overrides = []): MockInterface
	{
		$values = array_merge(self::PHOTO_CONFIGS, $overrides);
		$config = \Mockery::mock(ConfigManager::class);
		$config->shouldReceive('getValueAsString')->andReturnUsing(fn (string $key) => $values[$key]);
		$config->shouldReceive('getValueAsInt')->andReturnUsing(fn (string $key) => $values[$key]);
		$config->shouldReceive('getValueAsEnum')->andReturnUsing(fn (string $key, string $type) => $values[$key]);

		return $config;
	}

	private function photos(ColumnSortingType $column, TimelinePhotoGranularity $granularity = TimelinePhotoGranularity::MONTH, array $overrides = []): BucketScheme
	{
		return BucketScheme::forPhotos($column, $granularity, $this->config($overrides));
	}

	private function albums(ColumnSortingType $column, TimelineAlbumGranularity $granularity = TimelineAlbumGranularity::MONTH, array $overrides = []): BucketScheme
	{
		return BucketScheme::forAlbums($column, $granularity, $this->config($overrides));
	}

	// ── Prefix length ───────────────────────────────────────────────

	public function testPhotoDateIdsArePrefixesByGranularity(): void
	{
		foreach ([ColumnSortingType::TAKEN_AT, ColumnSortingType::CREATED_AT] as $column) {
			self::assertSame('SUBSTR(k, 1, 4)', $this->photos($column, TimelinePhotoGranularity::YEAR)->storedIdSql('k'));
			self::assertSame('SUBSTR(k, 1, 7)', $this->photos($column, TimelinePhotoGranularity::MONTH)->storedIdSql('k'));
			self::assertSame('SUBSTR(k, 1, 10)', $this->photos($column, TimelinePhotoGranularity::DAY)->storedIdSql('k'));
			self::assertSame('SUBSTR(k, 1, 13)', $this->photos($column, TimelinePhotoGranularity::HOUR)->storedIdSql('k'));
		}
	}

	public function testTitleDateIdsStopAtTheDay(): void
	{
		$scheme = $this->photos(ColumnSortingType::TITLE, TimelinePhotoGranularity::HOUR);

		self::assertSame('SUBSTR(k, 1, 10)', $scheme->storedIdSql('k'));
		self::assertSame('SUBSTR(photos.title_date_key, 1, 10)', $scheme->sourceIdSql());
		self::assertSame('SUBSTR(k, 1, 7)', $this->photos(ColumnSortingType::TITLE, TimelinePhotoGranularity::MONTH)->storedIdSql('k'));
	}

	public function testAlphabeticalIdsUseThePrefixLength(): void
	{
		$alphabetical = ['photo_title_bucket_mode' => TitleBucketMode::ALPHABETICAL];

		$scheme = $this->photos(ColumnSortingType::TITLE, overrides: $alphabetical + ['photo_title_bucket_prefix_length' => 3]);
		self::assertSame('SUBSTR(k, 1, 3)', $scheme->storedIdSql('k'));
		self::assertSame('SUBSTR(photos.title_base, 1, 3)', $scheme->sourceIdSql());

		self::assertSame('SUBSTR(k, 1, 1)', $this->photos(ColumnSortingType::TITLE, overrides: $alphabetical + ['photo_title_bucket_prefix_length' => 0])->storedIdSql('k'));
		self::assertSame('SUBSTR(k, 1, 255)', $this->photos(ColumnSortingType::TITLE, overrides: $alphabetical + ['photo_title_bucket_prefix_length' => 1000])->storedIdSql('k'));
	}

	public function testValueIdsAreTheWholeKey(): void
	{
		self::assertSame('k', $this->photos(ColumnSortingType::IS_HIGHLIGHTED)->storedIdSql('k'));
		self::assertSame('k', $this->photos(ColumnSortingType::TYPE)->storedIdSql('k'));
		self::assertSame('k', $this->photos(ColumnSortingType::RATING_AVG)->storedIdSql('k'));

		self::assertSame('photos.is_highlighted', $this->photos(ColumnSortingType::IS_HIGHLIGHTED)->sourceIdSql());
		self::assertSame('photos.type', $this->photos(ColumnSortingType::TYPE)->sourceIdSql());
		self::assertSame('ROUND(photos.rating_avg)', $this->photos(ColumnSortingType::RATING_AVG)->sourceIdSql());
	}

	public function testPhotoSourceColumns(): void
	{
		self::assertSame('SUBSTR(photos.taken_key, 1, 7)', $this->photos(ColumnSortingType::TAKEN_AT)->sourceIdSql());
		self::assertSame('photos.taken_key', $this->photos(ColumnSortingType::TAKEN_AT)->sourceColumn());
		self::assertSame('photos.created_at', $this->photos(ColumnSortingType::CREATED_AT)->sourceColumn());
		self::assertStringContainsString('photos.created_at', $this->photos(ColumnSortingType::CREATED_AT)->sourceIdSql());
	}

	public function testOwnerIdIsNotBucketable(): void
	{
		self::assertFalse($this->photos(ColumnSortingType::OWNER_ID)->isBucketable());
		self::assertFalse($this->albums(ColumnSortingType::OWNER_ID)->isBucketable());
		self::assertTrue($this->photos(ColumnSortingType::TAKEN_AT)->isBucketable());
	}

	public function testAlbumIdsArePrefixesByGranularity(): void
	{
		foreach ([ColumnSortingType::CREATED_AT, ColumnSortingType::MIN_TAKEN_AT, ColumnSortingType::MAX_TAKEN_AT, ColumnSortingType::TITLE] as $column) {
			self::assertSame('SUBSTR(k, 1, 4)', $this->albums($column, TimelineAlbumGranularity::YEAR)->storedIdSql('k'));
			self::assertSame('SUBSTR(k, 1, 7)', $this->albums($column, TimelineAlbumGranularity::MONTH)->storedIdSql('k'));
			self::assertSame('SUBSTR(k, 1, 10)', $this->albums($column, TimelineAlbumGranularity::DAY)->storedIdSql('k'));
		}

		$alphabetical = $this->albums(ColumnSortingType::TITLE, overrides: ['title_bucket_mode' => TitleBucketMode::ALPHABETICAL, 'title_bucket_prefix_length' => 2]);
		self::assertSame('SUBSTR(k, 1, 2)', $alphabetical->storedIdSql('k'));
	}

	// ── Next id ─────────────────────────────────────────────────────

	public function testNextDateIdAtEachPrecision(): void
	{
		self::assertSame('2027', BucketScheme::nextDateId('2026'));
		self::assertSame('2026-08', BucketScheme::nextDateId('2026-07'));
		self::assertSame('2027-01', BucketScheme::nextDateId('2026-12'));
		self::assertSame('2024-02-29', BucketScheme::nextDateId('2024-02-28'));
		self::assertSame('2026-03-01', BucketScheme::nextDateId('2026-02-28'));
		self::assertSame('2026-07-01-17', BucketScheme::nextDateId('2026-07-01-16'));
		self::assertSame('2027-01-01-00', BucketScheme::nextDateId('2026-12-31-23'));
	}

	public function testNextDateIdIgnoresTheServerZone(): void
	{
		$zone = date_default_timezone_get();
		date_default_timezone_set('Europe/Paris');
		try {
			// 02:00 does not exist in Paris on 2026-03-29; keys are wall-clock values of any zone.
			self::assertSame('2026-03-29-02', BucketScheme::nextDateId('2026-03-29-01'));
		} finally {
			date_default_timezone_set($zone);
		}
	}

	// ── Labels ──────────────────────────────────────────────────────

	public function testLabelsFollowTheIdPrecision(): void
	{
		$scheme = $this->photos(ColumnSortingType::TAKEN_AT, TimelinePhotoGranularity::HOUR);

		self::assertSame(['2026', 'Jul 2026', '1 Jul 2026', '4:00', 'unknown'], $scheme->labels(['2026', '2026-07', '2026-07-01', '2026-07-01-16', 'unknown']));
	}

	public function testHourLabelsOnOneDayUseTheSameDayFormat(): void
	{
		$scheme = $this->photos(ColumnSortingType::TAKEN_AT, TimelinePhotoGranularity::HOUR, ['timeline_photo_date_format_hour' => 'H:00']);

		self::assertSame(['18:00', '09:00', 'unknown'], $scheme->labels(['2026-10-14-18', '2026-10-14-09', 'unknown']));
	}

	public function testHourLabelsOverSeveralDaysUseTheMultiDayFormat(): void
	{
		$scheme = $this->photos(ColumnSortingType::CREATED_AT, TimelinePhotoGranularity::HOUR, ['timeline_photo_date_format_hour' => 'H:00']);

		self::assertSame(['14 Oct 2026 01:00', '13 Oct 2026 22:00', 'unknown'], $scheme->labels(['2026-10-14-01', '2026-10-13-22', 'unknown']));
	}

	public function testDayLabelsIgnoreTheHourFormats(): void
	{
		$scheme = $this->photos(ColumnSortingType::TAKEN_AT, TimelinePhotoGranularity::DAY);

		self::assertSame(['14 Oct 2026', '13 Oct 2026'], $scheme->labels(['2026-10-14', '2026-10-13']));
	}

	public function testTitleDateLabelsAtHourGranularityAreDays(): void
	{
		$scheme = $this->photos(ColumnSortingType::TITLE, TimelinePhotoGranularity::HOUR);

		self::assertSame(['15 Mar 2024'], $scheme->labels(['2024-03-15']));
	}

	public function testAlbumLabelsUseAlbumFormats(): void
	{
		$scheme = $this->albums(ColumnSortingType::MAX_TAKEN_AT, TimelineAlbumGranularity::DAY);

		self::assertSame(['2026', 'Jul 2026', '1 Jul'], $scheme->labels(['2026', '2026-07', '2026-07-01']));
	}

	public function testNonDateIdsAreTheirOwnLabels(): void
	{
		self::assertSame(['ab', 'unknown'], $this->photos(ColumnSortingType::TITLE, overrides: ['photo_title_bucket_mode' => TitleBucketMode::ALPHABETICAL])->labels(['ab', 'unknown']));
		self::assertSame(['1', '0'], $this->photos(ColumnSortingType::IS_HIGHLIGHTED)->labels(['1', '0']));
		self::assertSame(['image/jpeg'], $this->photos(ColumnSortingType::TYPE)->labels(['image/jpeg']));
		self::assertSame(['4'], $this->photos(ColumnSortingType::RATING_AVG)->labels(['4']));
	}

	/**
	 * S-092-23: keys written for another scheme, read before the recompute
	 * job has run.
	 */
	public function testDateSchemeIdsThatAreNotDateShapedAreTheirOwnLabels(): void
	{
		$albums = $this->albums(ColumnSortingType::TITLE, TimelineAlbumGranularity::DAY);
		self::assertSame(['2019', 'alph', '2020-03 tr', '2', '1 Jul'], $albums->labels(['2019', 'alph', '2020-03 tr', '2', '2026-07-01']));

		$photos = $this->photos(ColumnSortingType::TAKEN_AT, TimelinePhotoGranularity::HOUR);
		self::assertSame(['4:00', 'ab-cd-ef-gh'], $photos->labels(['2026-07-01-16', 'ab-cd-ef-gh']));
	}

	// ── Id normalisation ────────────────────────────────────────────

	public function testNormalizeId(): void
	{
		$flag = $this->photos(ColumnSortingType::IS_HIGHLIGHTED);
		self::assertSame(['1', '1', '1', '0', '0', '0'], array_map(fn ($v) => $flag->normalizeId($v), [true, 1, '1', false, 0, '0']));

		$rating = $this->photos(ColumnSortingType::RATING_AVG);
		self::assertSame(['4', '4', '5', '3'], array_map(fn ($v) => $rating->normalizeId($v), ['4.0', 4.4999, '4.5000', 3]));

		self::assertSame('unknown', $rating->normalizeId(null));
		self::assertSame('image/jpeg', $this->photos(ColumnSortingType::TYPE)->normalizeId('image/jpeg'));
		self::assertSame('2026-07', $this->photos(ColumnSortingType::TAKEN_AT)->normalizeId('2026-07'));
	}

	// ── Windows ─────────────────────────────────────────────────────

	public function testStoredDateWindowIsAKeyRange(): void
	{
		$query = DB::table('photo_album')->where($this->photos(ColumnSortingType::TAKEN_AT)->storedWindow('k', ['2026-07', 'unknown']));

		self::assertSame(['2026-07', '2026-08'], $query->getBindings());
		self::assertStringContainsString(self::wrap('k') . ' >= ?', $query->toSql());
		self::assertStringContainsString(self::wrap('k') . ' < ?', $query->toSql());
		self::assertStringContainsString(self::wrap('k') . ' is null', $query->toSql());
	}

	public function testStoredAlphabeticalWindowComparesThePrefix(): void
	{
		$scheme = $this->photos(ColumnSortingType::TITLE, overrides: ['photo_title_bucket_mode' => TitleBucketMode::ALPHABETICAL, 'photo_title_bucket_prefix_length' => 2]);
		$query = DB::table('photo_album')->where($scheme->storedWindow('k', ['ab']));

		self::assertSame(['ab'], $query->getBindings());
		self::assertStringContainsString('SUBSTR(k, 1, 2) = ?', $query->toSql());
	}

	public function testStoredValueWindowIsEquality(): void
	{
		$query = DB::table('photo_album')->where($this->photos(ColumnSortingType::RATING_AVG)->storedWindow('k', ['4', '5']));

		self::assertSame(['4', '5'], $query->getBindings());
	}

	public function testSourceWindows(): void
	{
		$created = DB::table('photos')->where($this->photos(ColumnSortingType::CREATED_AT)->sourceWindow(['2026-07']));
		self::assertSame(['2026-07-01 00:00:00', '2026-08-01 00:00:00'], $created->getBindings());

		$created_hour = DB::table('photos')->where($this->photos(ColumnSortingType::CREATED_AT, TimelinePhotoGranularity::HOUR)->sourceWindow(['2026-07-01-23']));
		self::assertSame(['2026-07-01 23:00:00', '2026-07-02 00:00:00'], $created_hour->getBindings());

		$taken = DB::table('photos')->where($this->photos(ColumnSortingType::TAKEN_AT)->sourceWindow(['2026-07']));
		self::assertSame(['2026-07', '2026-08'], $taken->getBindings());
		self::assertStringContainsString(self::wrap('photos.taken_key') . ' >= ?', $taken->toSql());

		$flag = DB::table('photos')->where($this->photos(ColumnSortingType::IS_HIGHLIGHTED)->sourceWindow(['1', '0']));
		self::assertSame([true, false], $flag->getBindings());

		$rating = DB::table('photos')->where($this->photos(ColumnSortingType::RATING_AVG)->sourceWindow(['4', 'unknown']));
		self::assertSame([3.5, 4.5], $rating->getBindings());
		self::assertStringContainsString(self::wrap('photos.rating_avg') . ' is null', $rating->toSql());

		$alphabetical = $this->photos(ColumnSortingType::TITLE, overrides: ['photo_title_bucket_mode' => TitleBucketMode::ALPHABETICAL]);
		$prefix = DB::table('photos')->where($alphabetical->sourceWindow(['a']));
		self::assertSame(['a'], $prefix->getBindings());
		self::assertStringContainsString('SUBSTR(photos.title_base, 1, 1) = ?', $prefix->toSql());
	}

	// ── Ordering and collection ─────────────────────────────────────

	public function testOrderRowsPutsUnknownLast(): void
	{
		$query = DB::table('photo_album');
		$this->photos(ColumnSortingType::TAKEN_AT)->orderRows($query, 'SUBSTR(k, 1, 7)', OrderSortingType::DESC);

		self::assertStringEndsWith('order by (SUBSTR(k, 1, 7) IS NULL) ASC, SUBSTR(k, 1, 7) desc', $query->toSql());
	}

	public function testCollectBucketsMovesUnknownLast(): void
	{
		$rows = [
			(object) ['bucket_id' => null, 'bucket_count' => 2],
			(object) ['bucket_id' => '2026', 'bucket_count' => '3'],
			(object) ['bucket_id' => '2025', 'bucket_count' => 1],
		];

		self::assertSame([['2026', '2025', 'unknown'], [3, 1, 2]], $this->photos(ColumnSortingType::TAKEN_AT, TimelinePhotoGranularity::YEAR)->collectBuckets($rows));
	}

	public function testCollectBucketsNormalisesIds(): void
	{
		$rows = [
			(object) ['bucket_id' => true, 'bucket_count' => 2],
			(object) ['bucket_id' => false, 'bucket_count' => 5],
		];

		self::assertSame([['1', '0'], [2, 5]], $this->photos(ColumnSortingType::IS_HIGHLIGHTED)->collectBuckets($rows));
	}

	/**
	 * Quotes a column the way the test connection does: `k` on MySQL, "k" on SQLite and PostgreSQL.
	 */
	private static function wrap(string $column): string
	{
		return DB::getQueryGrammar()->wrap($column);
	}
}
