<?php

namespace App\SmartAlbums;

use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = 'unsorted';
	public const TITLE = 'Unsorted';

	public function __construct()
	{
		parent::__construct(
			self::ID,
			self::TITLE,
			false,
			fn (Builder $q) => $q->whereNull('photos.album_id')
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
