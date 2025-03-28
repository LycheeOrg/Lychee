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
use App\Models\Configs;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RecentAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = SmartAlbumType::RECENT->value;

	/**
	 * @throws InvalidFormatException
	 * @throws InvalidTimeZoneException
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		$str_recent = $this->fromDateTime(
			Carbon::now()->subDays(Configs::getValueAsInt('recent_age'))
		);

		parent::__construct(
			id: SmartAlbumType::RECENT,
			smart_condition: function (Builder $query) use ($str_recent): void {
				$query->where('photos.created_at', '>=', $str_recent);
			}
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
