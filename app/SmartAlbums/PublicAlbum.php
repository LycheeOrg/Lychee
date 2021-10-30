<?php

namespace App\SmartAlbums;

use App\Relations\HasManyPhotosBySmartCondition;
use Illuminate\Database\Eloquent\Builder;

class PublicAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	const ID = 'public';
	const TITLE = 'Public';

	protected function __construct()
	{
		parent::__construct(self::ID, self::TITLE, false);
	}

	public static function getInstance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		// Actually, this statement is only needed due to testing.
		// The same instance of this class is used for all tests, because
		// the singleton stays alive during tests.
		// This implies that the relation of photos is never reloaded
		// but remains constant during all tests (it equals the empty set)
		// and the tests fail.
		self::$instance->unsetRelation('photos');

		return self::$instance;
	}

	public function photos(): HasManyPhotosBySmartCondition
	{
		// TODO: Check if the condition is actually what we want
		// Here we only return photos which are public on their own right.
		// This is the old behaviour, but the condition does not cover photos
		// which are public because they are part of a public album.
		// We probably should use
		// `PhotoAuthorisationProvider::applyPublicFilter()`
		// here.
		// This would also return consistent results with the RSS feed.
		return new HasManyPhotosBySmartCondition(
			$this,
			fn (Builder $q) => $q->where('photos.is_public', '=', true)
		);
	}
}
