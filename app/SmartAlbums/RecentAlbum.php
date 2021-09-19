<?php

namespace App\SmartAlbums;

use App\Models\Configs;
use App\Relations\HasManyPhotosBySmartCondition;
use Carbon\Exceptions\InvalidFormatException;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RecentAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	const ID = 'recent';
	const TITLE = 'Recent';

	protected function __construct()
	{
		parent::__construct(
			self::ID,
			self::TITLE,
			Configs::get_value('public_recent', '0') === '1'
		);
	}

	public static function getInstance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		// Actually, this statement is only needed due to testing.
		// The same instance of this class is used for all tests, because
		// the singleton stays alive during tests.
		// This implies that the relation of photos is never be reloaded
		// but remains constant during all tests (it equals the empty set)
		// and the tests fails.
		self::$instance->unsetRelation('photos');

		return self::$instance;
	}

	/**
	 * @throws InvalidFormatException
	 * @throws InvalidTimeZoneException
	 */
	public function photos(): HasManyPhotosBySmartCondition
	{
		$strRecent = $this->fromDateTime(
			Carbon::now()->subDays(intval(Configs::get_value('recent_age', '1')))
		);

		return new HasManyPhotosBySmartCondition(
			$this,
			function (Builder $query) use ($strRecent) {
				$query->where('created_at', '>=', $strRecent);
			}
		);
	}
}
