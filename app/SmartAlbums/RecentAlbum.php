<?php

namespace App\SmartAlbums;

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
	public const ID = 'recent';

	/**
	 * @throws InvalidFormatException
	 * @throws InvalidTimeZoneException
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		$strRecent = $this->fromDateTime(
			Carbon::now()->subDays(Configs::getValueAsInt('recent_age'))
		);

		parent::__construct(
			self::ID,
			__('lychee.RECENT'),
			Configs::getValueAsBool('public_recent'),
			function (Builder $query) use ($strRecent) {
				$query->where('photos.created_at', '>=', $strRecent);
			}
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
