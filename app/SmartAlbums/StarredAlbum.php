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
		// Actually, this statement is only needed due to testing.
		// The same instance of this class is used for all tests, because
		// the singleton stays alive during tests.
		// This implies that the relation of photos is never be reloaded
		// but remains constant during all tests (it equals the empty set)
		// and the tests fails.
		self::$instance->unsetRelation('photos');

		return self::$instance;
	}

	public function photos(): HasManyPhotosBySmartCondition
	{
		return new HasManyPhotosBySmartCondition(
			$this,
			fn (Builder $q) => $q->where('is_starred', '=', true)
		);
	}
}
