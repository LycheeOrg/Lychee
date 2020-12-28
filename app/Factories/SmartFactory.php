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
	public $base_smarts = [
		'unsorted' => UnsortedAlbum::class,
		'starred' => StarredAlbum::class,
		'public' => PublicAlbum::class,
		'recent' => RecentAlbum::class,
	];

	public function is_smart($kind)
	{
		return array_key_exists($kind, $this->base_smarts);
	}

	/**
	 * Factory method.
	 */
	public function make(string $kind): SmartAlbum
	{
		if ($this->is_smart($kind)) {
			return resolve($this->base_smarts[$kind]);
		}

		if ($kind == 'tag') {
			return resolve(TagAlbum::class);
		}

		return null;
	}

	public function makeAll(): Collection
	{
		$smartAlbums = new Collection();

		foreach (array_keys($this->base_smarts) as $smart_kind) {
			$smartAlbums->push($this->make($smart_kind));
		}

		return $smartAlbums;
	}
}
