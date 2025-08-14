<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Tag;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait TagCleanupTrait
{
	/**
	 * Cleans up tags that are not linked to any photos.
	 */
	protected function cleanupUnusedTags(): void
	{
		$ids = DB::table('tags')
			->select('id')
			->whereNotExists(
				function (Builder $query): void {
					$query->select(DB::raw(1))
						->from('photos_tags')
						->whereColumn('photos_tags.tag_id', 'tags.id');
				}
			)
			->pluck('id')
			->all();

		if (count($ids) === 0) {
			return;
		}

		// Just to be sure.
		DB::table('photos_tags')
			->whereIn('tag_id', $ids)
			->delete();

		// Remove any links to the tags in tag albums.
		DB::table('tag_albums_tags')
			->whereIn('tag_id', $ids)
			->delete();

		// Finally, delete the tags themselves.
		DB::table('tags')
			->whereIn('id', $ids)
			->delete();

		// Also remove the tag album if it does not have any tag left.
		DB::table('tag_albums')
			->whereNotExists(
				function (Builder $query): void {
					$query->select(DB::raw(1))
						->from('tag_albums_tags')
						->whereColumn('tag_albums_tags.album_id', 'tag_albums.id');
				}
			)->delete();
	}
}
