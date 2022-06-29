<?php

namespace App\SmartAlbums;

use App\Models\Configs;
use App\SmartAlbums\Utils\Wireable;
use Illuminate\Database\Eloquent\Builder;

class StarredAlbum extends BaseSmartAlbum
{
	use Wireable;

	private static ?self $instance = null;
	public const ID = 'starred';
	public const TITLE = 'Starred';

	protected function __construct()
	{
		parent::__construct(
			self::ID,
			self::TITLE,
			Configs::getValueAsBool('public_starred'),
			fn (Builder $q) => $q->where('photos.is_starred', '=', true)
		);
	}

	public static function getInstance(): self
	{
		self::$instance ??= new self();

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
