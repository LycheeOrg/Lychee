<?php

namespace App\Actions\Photo;

use App\Actions\User\Notify;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SetAlbum extends Setters
{
	public function __construct()
	{
		$this->property = 'album_id';
	}

	/**
	 * @throws ModelNotFoundException
	 * @throws QueryBuilderException
	 */
	public function do(array $photoIDs, ?string $albumID): void
	{
		if ($albumID) {
			Album::query()->findOrFail($albumID);

			foreach ($photoIDs as $id) {
				$photo = Photo::query()->find($id);
				$notify = new Notify();
				$notify->do($photo, $albumID);
			}
		}

		parent::do($photoIDs, $albumID == '0' ? null : $albumID);
	}
}
