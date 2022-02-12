<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;
use App\Exceptions\ModelDBException;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\Collection;

class Delete extends Action
{
	/**
	 * @param Collection<AbstractAlbum> $albums
	 *
	 * @throws ModelDBException
	 */
	public function do(Collection $albums): void
	{
		/** @var AbstractAlbum $album */
		foreach ($albums as $album) {
			if ($album instanceof BaseAlbum || $album instanceof UnsortedAlbum) {
				$album->delete();
			}
		}
	}
}
