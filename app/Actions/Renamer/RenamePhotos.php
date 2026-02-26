<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Renamer;

use App\Metadata\Renamer\PhotoRenamer;
use App\Models\Photo;
use Illuminate\Support\Collection;

class RenamePhotos
{
	private const CHUNK_SIZE = 100;

	/**
	 * Rename photos based on the user's renamer rules.
	 *
	 * @param int             $user_id   The user ID
	 * @param array<string>   $photo_ids Photo IDs to rename
	 * @param array<int>|null $rule_ids  The rule IDs to apply (null = all enabled rules)
	 *
	 * @return void
	 */
	public function execute(
		int $user_id,
		array $photo_ids,
		?array $rule_ids = null,
	): void {
		$photo_renamer = new PhotoRenamer($user_id, $rule_ids);

		Photo::query()
			->whereIn('id', $photo_ids)
			// Process by chunks of self::CHUNK_SIZE to avoid memory issues
			->chunkById(self::CHUNK_SIZE, function (Collection $photos) use ($photo_renamer): void {
				$values = $photos->map(fn (Photo $photo) => [
					'id' => $photo->id,
					'title' => $photo_renamer->handle($photo->title),
				])->all();

				// Make a batch update to update all photo titles at once
				$photo_instance = new Photo();
				// https://github.com/mavinoo/laravelBatch
				batch()->update($photo_instance, $values, 'id');
			});
	}
}
