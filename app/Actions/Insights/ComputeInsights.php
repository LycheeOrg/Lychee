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
 * Album and people counts read the core window in one query and the edge
 * strips by pages of photo IDs (Feature 108, NFR-108-02); an album scope is
 * streamed by pages of photo IDs (NFR-108-01).
 */
class ComputeInsights
{
	private const CHUNK = 1000;

	private LocalCaptureTime $clock;

	/**
	 * @param int $chunk rows per page of every stream (Feature 108, FR-108-02)
	 */
	public function __construct(private int $chunk = self::CHUNK)
	{
		$this->clock = new LocalCaptureTime();
	}

	/**
	 * @param int[]|null $known_years local years of the scope at the current
	 *                                revision, when already known (Feature 108,
	 *                                FR-108-03); ignored for the Library period
	 */
	public function do(InsightsScope $scope, InsightsPeriod $period, ?array $known_years = null): InsightsResource
	{
		$overview = new OverviewAccumulator();
		$time = new TimeAccumulator();
		$normaliser = new DeviceNormaliser();
		$devices = new DeviceAccumulator();
		$exposure = new ExposureAccumulator();
		$formats = new FormatAccumulator();
		$timeline = new TimelineAccumulator();

		foreach ($this->stream($scope, fn () => $this->window($this->projection(), $period)) as $raw) {
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
			years: $period->isLibrary() ? $time->years() : ($known_years ?? $this->years($scope)),
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

	/**
	 * The projection of NFR-085-06 over `photos`, without scope or period.
	 */
	private function projection(): Builder
	{
		return DB::table('photos')
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
	}

	/**
	 * Rows of a query over `photos` restricted to the scope, by ascending
	 * photo ID (Feature 108, NFR-108-01). Owner and whole-instance scopes page
	 * with `lazyById`; an album scope pages the tree's photo IDs and loads
	 * each page by ID.
	 *
	 * @param \Closure(): Builder $query a fresh query over `photos`, without the scope
	 *
	 * @return iterable<int,object>
	 */
	private function stream(InsightsScope $scope, \Closure $query): iterable
	{
		return $scope->isAlbum()
			? $this->albumStream($scope, $query)
			: InsightsScopeQuery::photos($query(), $scope)->lazyById($this->chunk, 'photos.id', 'id');
	}

	/**
	 * @param \Closure(): Builder $query a fresh query over `photos`, without the scope
	 *
	 * @return \Generator<int,object>
	 */
	private function albumStream(InsightsScope $scope, \Closure $query): \Generator
	{
		foreach (InsightsScopeQuery::photoIdPages($scope, $this->chunk) as $ids) {
			yield from $query()->whereIn('photos.id', $ids)->orderBy('photos.id')->get();
		}
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
		$rows = $this->stream($scope, fn () => DB::table('photos')
			->select(['photos.id', 'photos.taken_at', 'photos.taken_at_orig_tz'])
			->whereNotNull('photos.taken_at'));

		$years = [];
		foreach ($rows as $row) {
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

		return $this->distinctInPeriod(fn () => $this->albumLinks($scope), 'photo_album.album_id', $period);
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
		return $this->distinctInPeriod(fn () => InsightsScopeQuery::photos(DB::table('faces')->join('photos', 'photos.id', '=', 'faces.photo_id'), $scope)
			->where('faces.is_dismissed', '=', false)
			->whereNotNull('faces.person_id'), 'faces.person_id', $period);
	}

	/**
	 * Distinct values of `$column` over the rows whose photo was taken, in
	 * local time, within the period (Feature 108, NFR-108-02).
	 *
	 * The Library period is a plain distinct count. Otherwise one `DISTINCT`
	 * reads the core window, inside the period in every timezone; the photos
	 * of the edge strips are read one page of IDs at a time (`PeriodEdges`),
	 * each page bound by ID so the planner looks its photos up by primary key,
	 * and their rows are kept when their local date is in the period. The
	 * number of queries grows with the edge photos, not with links or faces;
	 * memory grows with the distinct values plus the rows of one page
	 * (NFR-108-04).
	 *
	 * @param \Closure(): Builder $query a fresh query joined to `photos`, scope applied
	 */
	private function distinctInPeriod(\Closure $query, string $column, InsightsPeriod $period): int
	{
		$core = $period->utcCore();
		if ($core === null) {
			return $query()->distinct()->count($column);
		}

		$values = array_fill_keys($query()->whereBetween('photos.taken_at', $core)->distinct()->pluck($column)->all(), true);
		foreach (PeriodEdges::photoIdPages($period, $this->chunk) as $ids) {
			$edges = $query()
				->whereIn('photos.id', $ids)
				->select([$column . ' as value', 'photos.taken_at', 'photos.taken_at_orig_tz'])
				->distinct()
				->get();
			foreach ($edges as $row) {
				$values[$row->value] ??= $this->belongs($period, $this->moment($row->taken_at, $row->taken_at_orig_tz)) ? true : null;
			}
		}

		return count(array_filter($values));
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
