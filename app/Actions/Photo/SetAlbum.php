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
		parent::do($photoIDs, $albumID);

		/** @var Album $album */
		$album = empty($albumID) ? null : Album::query()->findOrFail($albumID);

		if ($album) {
			Photo::query()->whereIn('id', $photoIDs)->update(['owner_id' => $album->owner_id]);
		}

		foreach ($photoIDs as $id) {
			$photo = Photo::query()->find($id);
			$notify = new Notify();
			$notify->do($photo);
		}
	}
}
