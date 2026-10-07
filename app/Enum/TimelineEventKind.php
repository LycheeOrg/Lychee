<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Kind of an Insights timeline event (Feature 085, FR-085-19). Cases are
 * listed in display order for events on the same day.
 */
enum TimelineEventKind: string
{
	case FIRST_CAPTURE = 'first_capture';
	case FIRST_VIDEO = 'first_video';
	case FIRST_LOCATED = 'first_located';
	case FIRST_WITH_PEOPLE = 'first_with_people';
	case FIRST_HIGHLIGHTED = 'first_highlighted';
	case DEVICE_FIRST = 'device_first';
	case MILESTONE = 'milestone';
	case LONGEST_VIDEO = 'longest_video';
	case LARGEST_FILE = 'largest_file';
	case HIGHEST_ISO = 'highest_iso';
	case LONGEST_EXPOSURE = 'longest_exposure';
	case WIDEST_APERTURE = 'widest_aperture';
	case LONGEST_FOCAL = 'longest_focal';
	case BREAK_START = 'break_start';
	case BREAK_END = 'break_end';
	case STREAK_START = 'streak_start';
	case STREAK_END = 'streak_end';
	case DEVICE_LAST = 'device_last';
	case LAST_CAPTURE = 'last_capture';

	public function category(): TimelineCategory
	{
		return match ($this) {
			self::FIRST_CAPTURE, self::LAST_CAPTURE, self::FIRST_VIDEO, self::FIRST_LOCATED, self::FIRST_WITH_PEOPLE, self::FIRST_HIGHLIGHTED => TimelineCategory::FIRST_LAST,
			self::DEVICE_FIRST, self::DEVICE_LAST => TimelineCategory::DEVICE,
			self::MILESTONE => TimelineCategory::MILESTONE,
			self::LONGEST_VIDEO, self::LARGEST_FILE, self::HIGHEST_ISO, self::LONGEST_EXPOSURE, self::WIDEST_APERTURE, self::LONGEST_FOCAL => TimelineCategory::RECORD,
			self::BREAK_START, self::BREAK_END, self::STREAK_START, self::STREAK_END => TimelineCategory::BREAK,
		};
	}

	/**
	 * Position among events of the same day.
	 */
	public function order(): int
	{
		return intval(array_search($this, self::cases(), true));
	}
}
