<?php

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Configs;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OnThisDayAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	// PHP 8.2
	// public const ID = SmartAlbumType::ON_THIS_DAY->value;
	public const ID = 'on_this_day';

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
			Configs::getValueAsBool('public_on_this_day'),
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
