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

		return self::$instance;
	}

	public function photos(): HasManyPhotosBySmartCondition
	{
		// TODO: Check if the condition is actually what we want
		// Here we only return photos which are public on their own right.
		// This is the old behaviour, but the condition does not cover photos
		// which are public because they are part of a public album.
		return new HasManyPhotosBySmartCondition(
			$this,
			fn (Builder $q) => $q->where('public', '=', true)
		);
	}
}
