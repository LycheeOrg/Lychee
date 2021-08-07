<?php

namespace App\SmartAlbums;

use App\Models\Configs;
use App\Relations\HasManyPhotosBySmartCondition;
use Illuminate\Database\Eloquent\Builder;

class StarredAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	const ID = 'starred';
	const TITLE = 'Starred';

	protected function __construct()
	{
		parent::__construct(
			self::ID,
			self::TITLE,
			Configs::get_value('public_starred', '0') === '1'
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
		return new HasManyPhotosBySmartCondition(
			$this,
			fn (Builder $q) => $q->where('star', '=', true)
		);
	}
}
