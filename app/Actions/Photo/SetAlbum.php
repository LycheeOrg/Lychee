<?php

namespace App\Actions\Photo;

use App\Actions\Album\UpdateTakestamps;
use App\Exceptions\JsonError;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Photo;

class SetAlbum extends Setters
{
	private $albumFactory;
	private $updateTakestamps;

	public function __construct(AlbumFactory $albumFactory, UpdateTakestamps $updateTakestamps)
	{
		$this->property = 'album_id';
		$this->albumFactory = $albumFactory;
		$this->updateTakestamps = $updateTakestamps;
	}

	public function execute(array $photoIDs, string $albumID)
	{
		$no_error = true;
		$album = null;

		if ($albumID != '0') {
			$album = $this->albumFactory->make($albumID);

			if ($album->is_tag_album()) {
				throw new JsonError('Sorry, cannot Set to tag Album.');
			}

			if ($album->is_smart()) {
				throw new JsonError('Sorry, cannot Set to smart Album.');
			}
		}

		$old_parents = Photo::select('album_id')->whereNotNull('album_id')->whereIn('id', $photoIDs)->pluck('album_id');

		$no_error &= $this->do($photoIDs, $albumID == '0' ? null : $albumID);

		if ($album !== null) {
			$no_error &= $this->updateTakestamps->singleAndSave($album);
		}

		$old_albums = Album::whereIn('id', $old_parents)->get();
		foreach ($old_albums as $old_album) {
			$no_error &= $this->updateTakestamps->singleAndSave($old_album);
		}

		return $no_error;
	}
}
