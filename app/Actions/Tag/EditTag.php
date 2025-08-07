<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
 * Once the migration is done, we can check if there are any tags with no link and clean up.
 */
class EditTag
{
	use TagCleanupTrait;

	public function do(Tag $old_tag, string $name): void
	{
		/** @var Tag $new_tag */
		$new_tag = Tag::where('name', $name)->first() ?? Tag::create(['name' => $name]);

		/** @var User $user */
		$user = Auth::user();

		DB::table('photos_tags')
			->where('tag_id', $old_tag->id)
			->when(
				$user->may_administrate === false,
				fn ($q) => $q
					->whereExists(fn (Builder $query) => $query->select(DB::raw(1))
							->from('photos')
							->whereColumn('photos.id', 'photo_id')
							->where('photos.owner_id', $user->id)
					)
			)
			->update(['tag_id' => $new_tag->id]);

		$this->cleanupUnusedTags();
	}
}
