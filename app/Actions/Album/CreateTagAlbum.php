<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\TagAlbum;
use Illuminate\Support\Facades\Auth;

class CreateTagAlbum extends Action
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
		$userId = Auth::id() ?? throw new UnauthenticatedException();

		$album = new TagAlbum();
		$album->title = $title;
		$album->show_tags = $show_tags;
		$album->owner_id = $userId;
		$album->save();

		return $album;
	}
}
