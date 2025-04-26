<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\Auth;

class CreateTagAlbum
{
	/**
	 * Create a new smart album based on tags.
	 *
	 * @param string   $title
	 * @param string[] $show_tags
	 *
	 * @return TagAlbum
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 */
	public function create(string $title, array $show_tags): TagAlbum
	{
		/** @var int */
		$user_id = Auth::id() ?? throw new UnauthenticatedException();

		$album = new TagAlbum();
		$album->title = $title;
		$album->show_tags = $show_tags;
		$album->owner_id = $user_id;
		$album->save();
		$this->setStatistics($album);

		return $album;
	}

	private function setStatistics(TagAlbum $album): void
	{
		$album->statistics()->create([
			'album_id' => $album->id,
			'photo_id' => null,
			'visit_count' => 0,
			'download_count' => 0,
			'favourite_count' => 0,
			'shared_count' => 0,
		]);
	}
}
