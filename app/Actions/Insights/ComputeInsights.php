<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights;

use App\Actions\Insights\Accumulators\DeviceAccumulator;
use App\Actions\Insights\Accumulators\ExposureAccumulator;
use App\Actions\Insights\Accumulators\FormatAccumulator;
use App\Actions\Insights\Accumulators\OverviewAccumulator;
use App\Actions\Insights\Accumulators\TimeAccumulator;
use App\Actions\Insights\Accumulators\TimelineAccumulator;
use App\Actions\Insights\Helpers\DeviceNormaliser;
use App\Actions\Insights\Helpers\LocalCaptureTime;
use App\Actions\Insights\Helpers\LocalMoment;
use App\Actions\Insights\Helpers\MilestoneFinder;
use App\DTO\Insights\InsightsPeriod;
use App\DTO\Insights\InsightsScope;
use App\Enum\SizeVariantType;
use App\Enum\TimelineEventKind;
use App\Http\Resources\Insights\InsightsResource;
use App\Http\Resources\Insights\TimelineEventData;
use App\Http\Resources\Insights\TimeSpanData;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Computes Insights for one scope and period (Feature 085, ADR-085-03).
 *
 * One streamed pass over a narrow projection of `photos` (with the file size
 * of the original size variant) feeds every
 * accumulator. Capture times are read in local time; a Year or Range period
 * is narrowed in SQL to a widened UTC window and filtered exactly in PHP.
 * Album and people counts come from small side queries filtered the same way.
 */
class ComputeInsights
{
	private const CHUNK = 1000;

	private LocalCaptureTime $clock;

	public function __construct()
	{
		$this->clock = new LocalCaptureTime();
	}

	public function do(InsightsScope $scope, InsightsPeriod $period): InsightsResource
	{
		$overview = new OverviewAccumulator();
		$time = new TimeAccumulator();
		$normaliser = new DeviceNormaliser();
		$devices = new DeviceAccumulator();
		$exposure = new ExposureAccumulator();
		$formats = new FormatAccumulator();
		$timeline = new TimelineAccumulator();

		foreach ($this->photos($scope, $period)->lazyById(self::CHUNK, 'photos.id', 'id') as $raw) {
			$row = PhotoRow::fromDatabase($raw);
			$moment = $this->moment($row->taken_at, $row->taken_at_orig_tz);
			if (!$this->belongs($period, $moment)) {
				continue;
			}

			$device = $normaliser->normalise($row->make, $row->model);
			$overview->add($row);
			$time->add($row, $moment);
			$devices->add($row, $device);
			$exposure->add($row);
			$formats->add($row);
			$timeline->add($row, $moment, $device->name);
		}

		$time_span = $time->toTimeSpan();

		return new InsightsResource(
			years: $period->isLibrary() ? $time->years() : $this->years($scope),
			overview: $overview->toOverview($this->albums($scope, $period)),
			storage: $overview->toStorage(),
			people: $overview->toPeople($this->hasFaces($scope), $this->people($scope, $period)),
			places: $overview->toPlaces(),
			time_span: $time_span,
			calendar: $time->toCalendar(),
			rhythm: $time->toRhythm(),
			devices: $devices->toDevices(),
			exposure: $exposure->toExposure(),
			formats: $formats->toFormats(),
			timeline: self::timeline($timeline->events(), $time_span, $time->dayCounts()),
		);
	}

	/**
	 * Accumulated events plus first/last capture, milestones and the bounds
	 * of the longest break and streak, by date then kind (FR-085-19).
	 *
	 * @param TimelineEventData[] $events
	 * @param array<string,int>   $day_counts local date → items, ascending
	 *
	 * @return TimelineEventData[]
	 */
	private static function timeline(array $events, TimeSpanData $time_span, array $day_counts): array
	{
		if ($time_span->first !== null && $time_span->last !== null) {
			$events[] = TimelineEventData::of(TimelineEventKind::FIRST_CAPTURE, substr($time_span->first->taken_at, 0, 10), photo_id: $time_span->first->photo_id);
			$events[] = TimelineEventData::of(TimelineEventKind::LAST_CAPTURE, substr($time_span->last->taken_at, 0, 10), photo_id: $time_span->last->photo_id);
		}
		foreach (MilestoneFinder::dates($day_counts) as $threshold => $date) {
			$events[] = TimelineEventData::of(TimelineEventKind::MILESTONE, $date, value: $threshold);
		}
		foreach ([
			[TimelineEventKind::BREAK_START, TimelineEventKind::BREAK_END, $time_span->longest_break],
			[TimelineEventKind::STREAK_START, TimelineEventKind::STREAK_END, $time_span->longest_daily_streak],
		] as [$start, $end, $span]) {
			if ($span !== null) {
				$events[] = TimelineEventData::of($start, $span->from, value: $span->length);
				$events[] = TimelineEventData::of($end, $span->to, value: $span->length);
			}
		}

		usort($events, fn (TimelineEventData $a, TimelineEventData $b) => [$a->date, $a->kind->order(), $a->subject ?? ''] <=> [$b->date, $b->kind->order(), $b->subject ?? '']);

		return $events;
	}

	private function photos(InsightsScope $scope, InsightsPeriod $period): Builder
	{
		$query = DB::table('photos')
			->select([
				'photos.id', 'photos.taken_at', 'photos.taken_at_orig_tz', 'photos.type',
				'photos.make', 'photos.model', 'photos.lens', 'photos.iso', 'photos.aperture',
				'photos.shutter', 'photos.focal', 'photos.duration', 'originals.filesize', 'originals.width', 'originals.height',
				'photos.face_count', 'photos.latitude', 'photos.longitude', 'photos.is_highlighted',
			])
			->selectRaw('EXISTS (SELECT 1 FROM photo_album WHERE photo_album.photo_id = photos.id) AS in_album')
			->leftJoin('size_variants as originals', fn (JoinClause $join) => $join
				->on('originals.photo_id', '=', 'photos.id')
				->where('originals.type', '=', SizeVariantType::ORIGINAL->value));

		return $this->window(InsightsScopeQuery::photos($query, $scope), $period);
	}

	private function moment(?string $taken_at, ?string $timezone): ?LocalMoment
	{
		return $taken_at === null ? null : $this->clock->convert($taken_at, $timezone);
	}

	private function belongs(InsightsPeriod $period, ?LocalMoment $moment): bool
	{
		return $period->isLibrary() || ($moment !== null && $period->includes($moment->date));
	}

	/**
	 * Local years with photos in the scope, whatever the period.
	 *
	 * @return int[]
	 */
	private function years(InsightsScope $scope): array
	{
		$query = InsightsScopeQuery::photos(DB::table('photos'), $scope)
			->select(['photos.id', 'photos.taken_at', 'photos.taken_at_orig_tz'])
			->whereNotNull('photos.taken_at');

		$years = [];
		foreach ($query->lazyById(self::CHUNK, 'photos.id', 'id') as $row) {
			$years[$this->clock->convert($row->taken_at, $row->taken_at_orig_tz)->year] = true;
		}
		$years = array_keys($years);
		sort($years);

		return $years;
	}

	/**
	 * Owned albums for the whole library; albums holding a photo of the
	 * period otherwise.
	 */
	private function albums(InsightsScope $scope, InsightsPeriod $period): int
	{
		if ($period->isLibrary()) {
			return InsightsScopeQuery::albums(DB::table('albums')->join('base_albums', 'base_albums.id', '=', 'albums.id'), $scope)->count();
		}

		$album_ids = [];
		$last_album = '';
		$last_photo = '';
		do {
			$rows = $this->window($this->albumLinks($scope), $period)
				->select(['photo_album.album_id', 'photo_album.photo_id', 'photos.taken_at', 'photos.taken_at_orig_tz'])
				->where(fn (Builder $q) => $q
					->where('photo_album.album_id', '>', $last_album)
					->orWhere(fn (Builder $q) => $q->where('photo_album.album_id', '=', $last_album)->where('photo_album.photo_id', '>', $last_photo)))
				->orderBy('photo_album.album_id')
				->orderBy('photo_album.photo_id')
				->limit(self::CHUNK)
				->get();

			foreach ($rows as $row) {
				$album_ids[$row->album_id] ??= $this->belongs($period, $this->moment($row->taken_at, $row->taken_at_orig_tz)) ? true : null;
				$last_album = $row->album_id;
				$last_photo = $row->photo_id;
			}
		} while ($rows->count() === self::CHUNK);

		return count(array_filter($album_ids));
	}

	/**
	 * Whether faces were ever detected on the scope's photos.
	 */
	private function hasFaces(InsightsScope $scope): bool
	{
		return InsightsScopeQuery::photos(DB::table('faces')->join('photos', 'photos.id', '=', 'faces.photo_id'), $scope)->exists();
	}

	/**
	 * Distinct people with a face (not dismissed) on a photo of the period.
	 */
	private function people(InsightsScope $scope, InsightsPeriod $period): int
	{
		$query = InsightsScopeQuery::photos(DB::table('faces')->join('photos', 'photos.id', '=', 'faces.photo_id'), $scope)
			->where('faces.is_dismissed', '=', false)
			->whereNotNull('faces.person_id');

		if ($period->isLibrary()) {
			return $query->distinct()->count('faces.person_id');
		}

		$people = [];
		$rows = $this->window($query, $period)->select(['faces.id', 'faces.person_id', 'photos.taken_at', 'photos.taken_at_orig_tz']);
		foreach ($rows->lazyById(self::CHUNK, 'faces.id', 'id') as $row) {
			$people[$row->person_id] ??= $this->belongs($period, $this->moment($row->taken_at, $row->taken_at_orig_tz)) ? true : null;
		}

		return count(array_filter($people));
	}

	/**
	 * Album–photo links of the scope: the scope's photos in any album, or in
	 * album scope the links to an album of the tree.
	 */
	private function albumLinks(InsightsScope $scope): Builder
	{
		$query = DB::table('photo_album')
			->join('photos', 'photos.id', '=', 'photo_album.photo_id')
			->join('albums', 'albums.id', '=', 'photo_album.album_id');

		return $scope->isAlbum()
			? $query->whereBetween('albums._lft', [$scope->album_left, $scope->album_right])
			: InsightsScopeQuery::photos($query, $scope);
	}

	private function window(Builder $query, InsightsPeriod $period): Builder
	{
		$window = $period->utcWindow();

		return $window === null ? $query : $query->whereBetween('photos.taken_at', $window);
	}
}
