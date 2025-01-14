<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OnThisDayAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
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
			SmartAlbumType::ON_THIS_DAY,
			function (Builder $query) use ($today) {
				$query->where(fn (Builder $q) => $q
					->whereMonth('photos.taken_at', '=', $today->month)
					->whereDay('photos.taken_at', '=', $today->day))
				->orWhere(fn (Builder $q) => $q
					->whereNull('photos.taken_at')
					->whereYear('photos.created_at', '<', $today->year)
					->whereMonth('photos.created_at', '=', $today->month)
					->whereDay('photos.created_at', '=', $today->day));
			}
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
