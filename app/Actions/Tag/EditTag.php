<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Tag;

use App\Models\Tag;

/**
 * The dummy approach would be to rename the tag directly in the database.
 * However this does not work if we are working in a multi user setting.
 *
 * If User A has photos with tag `car` and User B has photos with tag `car`,
 * the renaming from User A should not impact User B.
 *
 * First we check if there is a tag that already exists with that name.
 * If there is one found, we just need to migrate the photos.
 * If there are no tag found, we can create a new one and migrate the photos to that one.
 *
 * In the end we just merge the old tag into the new one.
 * If the old tag has no more relationships, we delete it.
 */
class EditTag
{
	use TagCleanupTrait;

	public function do(Tag $old_tag, string $name): void
	{
		/** @var Tag $new_tag */
		$new_tag = Tag::where('name', $name)->first() ?? Tag::create(['name' => $name]);

		$merge = resolve(MergeTag::class);
		$merge->do(
			source: $old_tag,
			into: $new_tag
		);

		$this->cleanupUnusedTags();
	}
}
