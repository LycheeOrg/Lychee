<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Accumulators;

use App\Actions\Insights\Helpers\ExposureParser;
use App\Actions\Insights\Helpers\LocalMoment;
use App\Actions\Insights\PhotoRow;
use App\Enum\TimelineEventKind;
use App\Http\Resources\Insights\TimelineEventData;

/**
 * Firsts, first and last photo per device, and records of the dated items
 * (Feature 085, FR-085-19). Earlier capture wins a tie for firsts and
 * records; later capture wins for "last".
 *
 * Memory grows with the number of devices, not with the number of items.
 */
final class TimelineAccumulator
{
	/** @var array<string,array{0:PhotoRow,1:LocalMoment}> kind → earliest matching item */
	private array $firsts = [];

	/** @var array<string,array{first:array{0:PhotoRow,1:LocalMoment},last:array{0:PhotoRow,1:LocalMoment}}> device → items */
	private array $devices = [];

	/** @var array<string,array{0:PhotoRow,1:LocalMoment,2:float}> kind → record item and value */
	private array $records = [];

	public function add(PhotoRow $row, ?LocalMoment $moment, ?string $device): void
	{
		if ($moment === null || $row->taken_at === null) {
			return;
		}

		$this->first(TimelineEventKind::FIRST_VIDEO, $row->is_video, $row, $moment);
		$this->first(TimelineEventKind::FIRST_LOCATED, $row->is_located, $row, $moment);
		$this->first(TimelineEventKind::FIRST_WITH_PEOPLE, $row->face_count > 0, $row, $moment);
		$this->first(TimelineEventKind::FIRST_HIGHLIGHTED, $row->is_highlighted, $row, $moment);
		$this->device($device, $row, $moment);

		$this->record(TimelineEventKind::LARGEST_FILE, $row->filesize > 0 ? floatval($row->filesize) : null, $row, $moment);
		if ($row->is_video) {
			$this->record(TimelineEventKind::LONGEST_VIDEO, ExposureParser::duration($row->duration), $row, $moment);

			return;
		}

		$this->record(TimelineEventKind::HIGHEST_ISO, ExposureParser::iso($row->iso), $row, $moment);
		$this->record(TimelineEventKind::LONGEST_EXPOSURE, ExposureParser::shutter($row->shutter), $row, $moment);
		$this->record(TimelineEventKind::LONGEST_FOCAL, ExposureParser::focal($row->focal), $row, $moment);
		$aperture = ExposureParser::aperture($row->aperture);
		// Widest aperture is the smallest f-number: compare negated values.
		$this->record(TimelineEventKind::WIDEST_APERTURE, $aperture === null ? null : -$aperture, $row, $moment);
	}

	/**
	 * @return TimelineEventData[]
	 */
	public function events(): array
	{
		$events = [];
		foreach ($this->firsts as $kind => [$row, $moment]) {
			$events[] = TimelineEventData::of(TimelineEventKind::from($kind), $moment->date, photo_id: $row->id);
		}
		foreach ($this->devices as $name => $seen) {
			$events[] = TimelineEventData::of(TimelineEventKind::DEVICE_FIRST, $seen['first'][1]->date, strval($name), photo_id: $seen['first'][0]->id);
			$events[] = TimelineEventData::of(TimelineEventKind::DEVICE_LAST, $seen['last'][1]->date, strval($name), photo_id: $seen['last'][0]->id);
		}
		foreach ($this->records as $kind => [$row, $moment, $value]) {
			$kind = TimelineEventKind::from($kind);
			$events[] = TimelineEventData::of($kind, $moment->date, value: $kind === TimelineEventKind::WIDEST_APERTURE ? -$value : $value, photo_id: $row->id);
		}

		return $events;
	}

	private function first(TimelineEventKind $kind, bool $matches, PhotoRow $row, LocalMoment $moment): void
	{
		if ($matches && (!isset($this->firsts[$kind->value]) || $row->taken_at < $this->firsts[$kind->value][0]->taken_at)) {
			$this->firsts[$kind->value] = [$row, $moment];
		}
	}

	private function device(?string $device, PhotoRow $row, LocalMoment $moment): void
	{
		if ($device === null) {
			return;
		}

		$seen = $this->devices[$device] ?? ['first' => [$row, $moment], 'last' => [$row, $moment]];
		if ($row->taken_at < $seen['first'][0]->taken_at) {
			$seen['first'] = [$row, $moment];
		}
		if ($row->taken_at >= $seen['last'][0]->taken_at) {
			$seen['last'] = [$row, $moment];
		}
		$this->devices[$device] = $seen;
	}

	/**
	 * Keeps the item with the highest value; the earlier capture on a tie.
	 */
	private function record(TimelineEventKind $kind, ?float $value, PhotoRow $row, LocalMoment $moment): void
	{
		if ($value === null) {
			return;
		}

		$current = $this->records[$kind->value] ?? null;
		if ($current === null || $value > $current[2] || ($value === $current[2] && $row->taken_at < $current[0]->taken_at)) {
			$this->records[$kind->value] = [$row, $moment, $value];
		}
	}
}
