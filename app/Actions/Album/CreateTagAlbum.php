<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Tag;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\Auth;

class CreateTagAlbum
{
	/**
	 * Create a new smart album based on tags.
	 *
	 * @param string   $title
	 * @param string[] $tags
	 * @param bool     $is_and
	 *
	 * @return TagAlbum
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 */
	public function create(string $title, array $tags, bool $is_and): TagAlbum
	{
		/** @var int */
		$user_id = Auth::id() ?? throw new UnauthenticatedException();

		$album = new TagAlbum();
		$album->title = $title;
		$album->owner_id = $user_id;
		$album->is_and = $is_and;
		$album->save();

		$tag_models = Tag::from($tags);
		$album->tags()->sync($tag_models->pluck('id')->all());

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
