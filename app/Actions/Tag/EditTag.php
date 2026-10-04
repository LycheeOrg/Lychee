<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Tag;

use App\Events\AlbumTagsChanged;
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
 *
 * A rename to the same name up to case (`toronto` => `Toronto`) is applied to the tag itself.
 * The unique index on `tags.name` and the case-insensitive collation of MySQL/MariaDB
 * do not allow both spellings to exist, so this rename is visible to all users.
 */
class EditTag
{
	use TagCleanupTrait;

	public function do(Tag $old_tag, string $name): void
	{
		/** @var Tag|null $new_tag */
		$new_tag = Tag::where('name', $name)->first();

		if ($this->isSameTag($old_tag, $new_tag, $name)) {
			$old_tag->name = $name;
			$old_tag->save();
			AlbumTagsChanged::dispatch([$old_tag->id]);

			return;
		}

		$new_tag ??= Tag::create(['name' => $name]);

		$merge = resolve(MergeTag::class);
		$merge->do(
			source: $old_tag,
			into: $new_tag
		);

		$this->cleanupUnusedTags();
	}

	/**
	 * Whether the new name designates the tag being renamed.
	 * On MySQL/MariaDB the lookup by name already matches the old tag (case and accents are ignored),
	 * on PostgreSQL/SQLite it finds nothing and we compare the names ignoring case.
	 */
	private function isSameTag(Tag $old_tag, ?Tag $found, string $name): bool
	{
		return $found?->id === $old_tag->id ||
			($found === null && mb_strtolower($old_tag->name) === mb_strtolower($name));
	}
}
