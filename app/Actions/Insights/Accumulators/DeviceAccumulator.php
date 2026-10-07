<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Insights\Accumulators;

use App\Actions\Insights\Helpers\Device;
use App\Actions\Insights\Helpers\Distribution;
use App\Actions\Insights\Helpers\ExposureParser;
use App\Actions\Insights\PhotoRow;
use App\Enum\DeviceCategory;
use App\Http\Resources\Insights\DeviceEntryData;
use App\Http\Resources\Insights\DeviceFocalData;
use App\Http\Resources\Insights\DevicesData;

/**
 * Counts per device, manufacturer and lens (Feature 085, FR-085-12).
 *
 * Entries are keyed by (name, category) so that a manufacturer making both
 * phones and cameras, or a lens used on both, can be filtered by category;
 * the client sums entries of the same name for "all categories".
 */
final class DeviceAccumulator
{
	private const ALL = 0;
	private const PHOTOS = 1;
	private const VIDEOS = 2;
	private const HIGHLIGHTED = 3;
	private const LOCATED = 4;
	private const WITH_PEOPLE = 5;

	/** @var array<string,array{name:string|null,category:DeviceCategory,counts:int[]}> */
	private array $devices = [];

	/** @var array<string,array{name:string|null,category:DeviceCategory,counts:int[]}> */
	private array $manufacturers = [];

	/** @var array<string,array{name:string|null,category:DeviceCategory,counts:int[]}> */
	private array $lenses = [];

	/** @var array<string,array{name:string|null,category:DeviceCategory,focal:Distribution}> */
	private array $focal_lengths = [];

	public function add(PhotoRow $row, Device $device): void
	{
		$lens = trim($row->lens ?? '');
		$flags = [
			self::ALL => 1,
			self::PHOTOS => intval($row->is_image),
			self::VIDEOS => intval($row->is_video),
			self::HIGHLIGHTED => intval($row->is_highlighted),
			self::LOCATED => intval($row->is_located),
			self::WITH_PEOPLE => intval($row->face_count > 0),
		];

		self::count($this->devices, $device->name, $device->category, $flags);
		self::count($this->manufacturers, $device->manufacturer, $device->category, $flags);
		self::count($this->lenses, $lens === '' ? null : $lens, $device->category, $flags);
		$this->addFocal($row, $device);
	}

	public function toDevices(): DevicesData
	{
		return new DevicesData(
			devices: self::entries($this->devices),
			manufacturers: self::entries($this->manufacturers),
			lenses: self::entries($this->lenses),
			focal_lengths: $this->focalLengths(),
		);
	}

	private function addFocal(PhotoRow $row, Device $device): void
	{
		$focal = $row->is_video ? null : ExposureParser::focal($row->focal);
		if ($focal === null) {
			return;
		}

		$key = ($device->name ?? '') . "\0" . $device->category->value;
		$this->focal_lengths[$key] ??= ['name' => $device->name, 'category' => $device->category, 'focal' => new Distribution()];
		$this->focal_lengths[$key]['focal']->add($focal);
	}

	/**
	 * Devices with a focal length, most photos first.
	 *
	 * @return DeviceFocalData[]
	 */
	private function focalLengths(): array
	{
		$list = array_map(fn (array $entry) => [$entry, $entry['focal']->summarise()], array_values($this->focal_lengths));
		usort($list, fn (array $a, array $b) => [$b[1]->total, $a[0]['name'] === null, $a[0]['name'] ?? ''] <=> [$a[1]->total, $b[0]['name'] === null, $b[0]['name'] ?? '']);

		return array_map(fn (array $item) => new DeviceFocalData(
			name: $item[0]['name'],
			category: $item[0]['category'],
			values: $item[1]->values,
			counts: $item[1]->counts,
		), $list);
	}

	/**
	 * @param array<string,array{name:string|null,category:DeviceCategory,counts:int[]}> $entries
	 * @param int[]                                                                      $flags
	 */
	private static function count(array &$entries, ?string $name, DeviceCategory $category, array $flags): void
	{
		$key = ($name ?? '') . "\0" . $category->value;
		$entries[$key] ??= ['name' => $name, 'category' => $category, 'counts' => array_fill(0, count($flags), 0)];
		foreach ($flags as $i => $flag) {
			$entries[$key]['counts'][$i] += $flag;
		}
	}

	/**
	 * Most used first; equal counts by name, unknown last.
	 *
	 * @param array<string,array{name:string|null,category:DeviceCategory,counts:int[]}> $entries
	 *
	 * @return DeviceEntryData[]
	 */
	private static function entries(array $entries): array
	{
		$list = array_values($entries);
		usort($list, fn (array $a, array $b) => [$b['counts'][self::ALL], $a['name'] === null, $a['name'] ?? ''] <=> [$a['counts'][self::ALL], $b['name'] === null, $b['name'] ?? '']);

		return array_map(fn (array $entry) => new DeviceEntryData(
			name: $entry['name'],
			category: $entry['category'],
			all: $entry['counts'][self::ALL],
			photos: $entry['counts'][self::PHOTOS],
			videos: $entry['counts'][self::VIDEOS],
			highlighted: $entry['counts'][self::HIGHLIGHTED],
			located: $entry['counts'][self::LOCATED],
			with_people: $entry['counts'][self::WITH_PEOPLE],
		), $list);
	}
}
