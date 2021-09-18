<?php

namespace App\Actions\Photo;

use App\Models\Photo;
use App\SmartAlbums\StarredAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Random
{
	/**
	 * @return Photo
	 *
	 * @throws ModelNotFoundException
	 */
	public function do(): Photo
	{
		/* @noinspection PhpIncompatibleReturnTypeInspection */
		return StarredAlbum::getInstance()
			->photos()
			->inRandomOrder()
			->firstOrFail();
	}
}
