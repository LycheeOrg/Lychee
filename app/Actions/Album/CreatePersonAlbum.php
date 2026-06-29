<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\PersonAlbum;
use Illuminate\Support\Facades\Auth;

class CreatePersonAlbum
{
	/**
	 * Create a new smart album based on persons.
	 *
	 * @param string   $title
	 * @param string[] $person_ids
	 * @param bool     $is_and
	 *
	 * @return PersonAlbum
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 */
	public function create(string $title, array $person_ids, bool $is_and): PersonAlbum
	{
		/** @var int */
		$user_id = Auth::id() ?? throw new UnauthenticatedException();

		$album = new PersonAlbum();
		$album->title = $title;
		$album->owner_id = $user_id;
		$album->is_and = $is_and;
		$album->save();

		$album->persons()->sync($person_ids);

		$this->setStatistics($album);

		return $album;
	}

	private function setStatistics(PersonAlbum $album): void
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
