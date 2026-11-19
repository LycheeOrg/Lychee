<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Accumulators;

use App\Actions\Insights\Helpers\LocalMoment;
use App\Actions\Insights\Helpers\StreakCalculator;
use App\Actions\Insights\PhotoRow;
use App\Http\Resources\Insights\BusiestDayData;
use App\Http\Resources\Insights\CalendarData;
use App\Http\Resources\Insights\CapturePointData;
use App\Http\Resources\Insights\RhythmData;
use App\Http\Resources\Insights\SpanData;
use App\Http\Resources\Insights\TimeSpanData;

/**
 * Time-based figures in local capture time: time span, calendar and rhythm
 * (Feature 085, FR-085-09 … FR-085-11, FR-085-15).
 *
 * Memory grows with the number of photo days, not with the number of photos.
 */
final class TimeAccumulator
{
	/** @var array<string,int> local date → photos */
	private array $day_counts = [];

	/** @var array<string,string> local date → first photo seen on that day */
	private array $day_photos = [];

	/** @var int[][] */
	private array $week_hour;

	/** @var int[] */
	private array $months;

	/** @var array<int,true> */
	private array $years = [];

	private ?PhotoRow $first = null;
	private ?LocalMoment $first_moment = null;
	private ?PhotoRow $last = null;
	private ?LocalMoment $last_moment = null;
	private int $undated = 0;

	public function __construct()
	{
		$this->week_hour = array_fill(0, 7, array_fill(0, 24, 0));
		$this->months = array_fill(0, 12, 0);
	}

	public function add(PhotoRow $row, ?LocalMoment $moment): void
	{
		if ($moment === null || $row->taken_at === null) {
			$this->undated++;

			return;
		}

		$this->day_counts[$moment->date] = ($this->day_counts[$moment->date] ?? 0) + 1;
		$this->day_photos[$moment->date] ??= $row->id;
		$this->week_hour[$moment->weekday][$moment->hour]++;
		$this->months[$moment->month - 1]++;
		$this->years[$moment->year] = true;

		if ($this->first === null || $row->taken_at < $this->first->taken_at) {
			$this->first = $row;
			$this->first_moment = $moment;
		}
		if ($this->last === null || $row->taken_at > $this->last->taken_at) {
			$this->last = $row;
			$this->last_moment = $moment;
		}
	}

	/**
	 * @return int[] local years with photos, ascending
	 */
	public function years(): array
	{
		$years = array_keys($this->years);
		sort($years);

		return $years;
	}

	public function toTimeSpan(): TimeSpanData
	{
		ksort($this->day_counts);
		$summary = StreakCalculator::summarise(array_keys($this->day_counts));

		return new TimeSpanData(
			first: self::capturePoint($this->first, $this->first_moment),
			last: self::capturePoint($this->last, $this->last_moment),
			busiest_day: $this->busiestDay(),
			days_with_photos: $summary->days_with_photos,
			calendar_days: $summary->calendar_days,
			undated: $this->undated,
			longest_break: SpanData::fromSpan($summary->longest_break),
			longest_daily_streak: SpanData::fromSpan($summary->longest_daily_streak),
			longest_weekly_streak: SpanData::fromSpan($summary->longest_weekly_streak),
		);
	}

	/**
	 * @return array<string,int> local date → photos, ascending
	 */
	public function dayCounts(): array
	{
		ksort($this->day_counts);

		return $this->day_counts;
	}

	public function toCalendar(): CalendarData
	{
		$counts = $this->dayCounts();

		return new CalendarData(
			dates: array_map(strval(...), array_keys($counts)),
			counts: array_values($counts),
		);
	}

	public function toRhythm(): RhythmData
	{
		$hours = array_fill(0, 24, 0);
		foreach ($this->week_hour as $day) {
			foreach ($day as $hour => $count) {
				$hours[$hour] += $count;
			}
		}

		return new RhythmData(
			week_hour: $this->week_hour,
			months: $this->months,
			weekdays: array_map(array_sum(...), $this->week_hour),
			hours: $hours,
		);
	}

	private function busiestDay(): ?BusiestDayData
	{
		ksort($this->day_counts);
		$best = null;
		foreach ($this->day_counts as $date => $count) {
			if ($best === null || $count > $best->count) {
				$best = new BusiestDayData(strval($date), $count, $this->day_photos[$date]);
			}
		}

		return $best;
	}

	private static function capturePoint(?PhotoRow $row, ?LocalMoment $moment): ?CapturePointData
	{
		if ($row === null || $moment === null) {
			return null;
		}

		return new CapturePointData($row->id, $moment->date . ' ' . $moment->time);
	}
}
