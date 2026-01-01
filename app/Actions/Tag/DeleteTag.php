<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Tag;

use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * We cannot delete tags directly due to foreign keys.
 * We must first delete the tag-photo relationship then the tag.
 *
 * However if User A has tagged photos with `car` and User B has also tagged photos with `car`
 * We should only delete the links of photos to tag for user A.
 *
 * Then we delete the tag only if there are no links remaining.
 *
 * Of course this does not apply if the user is an administrator.
 * In that case we delete the links and the tag regardless of the user.
 */
class DeleteTag
{
	use TagCleanupTrait;

	/**
	 * @param int[] $tag_ids
	 *
	 * @return void
	 */
	public function do(array $tag_ids): void
	{
		/** @var User $user */
		$user = Auth::user();

		DB::table('photos_tags')
			->whereIn('tag_id', $tag_ids)
			->when(
				$user->may_administrate === false,
				fn ($q) => $q
					->whereExists(fn (Builder $query) => $query->select(DB::raw(1))
							->from('photos')
							->whereColumn('photos.id', 'photos_tags.photo_id')
							->where('photos.owner_id', $user->id))
			)
			->delete();

		$this->cleanupUnusedTags();
	}
}
