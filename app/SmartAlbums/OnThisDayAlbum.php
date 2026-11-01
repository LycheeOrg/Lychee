<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Services\MonthDayKey;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OnThisDayAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::ON_THIS_DAY->value;

	/**
	 * @throws InvalidFormatException
	 * @throws InvalidTimeZoneException
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		$today = Carbon::today();

		parent::__construct(
			id: SmartAlbumType::ON_THIS_DAY,
			// `month_day_key` is the day where the photo was taken, else its
			// upload day (FR-106-01), read through its index; an upload of
			// this year does not count.
			smart_condition: function (Builder $query) use ($today): void {
				$query->where('photos.month_day_key', '=', MonthDayKey::ofDate($today))
					->where(fn (Builder $q) => $q
						->whereNotNull('photos.taken_at')
						->orWhere('photos.created_at', '<', $today->year . '-01-01 00:00:00'));
			}
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Membership follows the current date, so a cover cached on a previous
	 * day no longer belongs here. Checked through the album's own query so
	 * the date comparison matches the live one exactly.
	 */
	public function isCachedThumbValid(string $photo_id): bool
	{
		return $this->containsPhoto($photo_id);
	}
}
