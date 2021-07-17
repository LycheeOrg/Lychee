<?php

namespace App\Factories;

use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\SmartAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\TagAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Support\Collection;

class SmartFactory
{
	/**
	 * @var array
	 */
	const BASE_SMARTS = [
		'unsorted' => UnsortedAlbum::class,
		'starred' => StarredAlbum::class,
		'public' => PublicAlbum::class,
		'recent' => RecentAlbum::class,
	];

	public function is_smart($kind): bool
	{
		return array_key_exists($kind, self::BASE_SMARTS);
	}

	/**
	 * Factory method.
	 */
	public function make(string $kind): ?SmartAlbum
	{
		if ($this->is_smart($kind)) {
			return resolve(self::BASE_SMARTS[$kind]);
		}

		if ($kind == 'tag') {
			return resolve(TagAlbum::class);
		}

		return null;
	}

	public function makeAll(): Collection
	{
		$smartAlbums = new Collection();

		foreach (self::BASE_SMARTS as $smart_kind => $_) {
			$smartAlbums->push($this->make($smart_kind));
		}

		return $smartAlbums;
	}
}
