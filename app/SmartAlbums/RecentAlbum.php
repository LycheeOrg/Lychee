<?php

namespace App\SmartAlbums;

use App\Models\Configs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RecentAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	const ID = 'recent';
	const TITLE = 'Recent';

	protected function __construct()
	{
		$strRecent = $this->fromDateTime(
			Carbon::now()->subDays(intval(Configs::get_value('recent_age', '1')))
		);

		parent::__construct(
			self::ID,
			self::TITLE,
			Configs::get_value('public_recent', '0') === '1',
			function (Builder $query) use ($strRecent) {
				$query->where('photos.created_at', '>=', $strRecent);
			}
		);
	}

	public static function getInstance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		// The following two lines are only needed due to testing.
		// The same instance of this class is used for all tests, because
		// the singleton stays alive during tests.
		// This implies that the relation of photos is never be reloaded
		// but remains constant during all tests (it equals the empty set)
		// and the tests fails.
		unset(self::$instance->photos);
		unset(self::$instance->thumb);

		return self::$instance;
	}
}
