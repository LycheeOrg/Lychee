<?php

namespace App\SmartAlbums;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OnThisDayAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = 'on_this_day';
	public const TITLE = 'On This Day';

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
			self::ID,
			self::TITLE,
			false,
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
