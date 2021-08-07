<?php

namespace App\SmartAlbums;

use App\Models\Configs;
use App\Relations\HasManyPhotosBySmartCondition;
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

		return self::$instance;
	}

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
