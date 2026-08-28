<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Renamer;

use App\Metadata\Renamer\AlbumRenamer;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Services\TitleSplitter;
use Illuminate\Support\Collection;

class RenameAlbums
{
	private const CHUNK_SIZE = 100;

	/**
	 * Rename albums based on the user's renamer rules.
	 *
	 * @param int             $user_id   The user ID
	 * @param array<string>   $album_ids Album IDs to rename
	 * @param array<int>|null $rule_ids  The rule IDs to apply (null = all enabled rules)
	 *
	 * @return void
	 */
	public function execute(
		int $user_id,
		array $album_ids,
		?array $rule_ids = null,
	): void {
		$album_renamer = new AlbumRenamer($user_id, $rule_ids);

		Album::query()
			->whereIn('id', $album_ids)
			// Process by chunks of self::CHUNK_SIZE to avoid memory issues
			->chunkById(self::CHUNK_SIZE, function (Collection $albums) use ($album_renamer): void {
				// Feature 060 (FR-060-03): explicit sync, no model event.
				// `batch()->update()` bypasses `save()`/model events entirely,
				// so `title_base`/`title_index` must be computed inline here.
				$values = $albums->map(function (Album $album) use ($album_renamer) {
					$title = $album_renamer->handle($album->title);
					$title_split = TitleSplitter::split($title);

					return [
						'id' => $album->id,
						'title' => $title,
						'title_base' => $title_split->base,
						'title_index' => $title_split->index,
					];
				})->all();

				// Make a batch update to update all photo titles at once
				$album_instance = new BaseAlbumImpl();
				// https://github.com/mavinoo/laravelBatch
				batch()->update($album_instance, $values, 'id');
			});
	}
}
