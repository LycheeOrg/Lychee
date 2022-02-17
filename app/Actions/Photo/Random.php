<?php

namespace App\Actions\Photo;

use App\Contracts\InternalLycheeException;
use App\Models\Photo;
use App\SmartAlbums\StarredAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Random
{
	/**
	 * @return Photo
	 *
	 * @throws InternalLycheeException
	 * @throws \InvalidArgumentException
	 * @throws ModelNotFoundException
	 *
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public function do(): Photo
	{
		return StarredAlbum::getInstance()
			->photos()
			->inRandomOrder()
			->firstOrFail();
	}
}
