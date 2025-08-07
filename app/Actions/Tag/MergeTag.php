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
 * A dummy implementation would just tranfer all the pictures from one tag to another,
 * and then delete the source tag.
 *
 * This is unfortunately not that simple: if User A has tagged photos with `car` and User B has also tagged photos with `car`,
 * we should not touch the tag `car` if user B wants to merge it with another tag.
 *
 * For this we first figure out which are the photos which belongs to current user and the source tag.
 * We check if those pictures also have the destination tag also attached (avoid duplication).
 * Then we insert the missing tag-photo relationships.
 *
 * Finally we delete relationships between the source tag and the photos from the current user.
 *
 * If the source tag has no more relationships, we delete it.
 */
class MergeTag
{
	use TagCleanupTrait;

	public function do(Tag $source, Tag $into): void
	{
		/** @var User $user */
		$user = Auth::user();

		// We filter here the photos owned by the user.
		$source_photo_ids = DB::table('photos_tags')
			->where('tag_id', $source->id)
			->when(
				$user->may_administrate === false,
				fn ($q) => $q
					->whereExists(fn (Builder $query) => $query->select(DB::raw(1))
							->from('photos')
							->whereColumn('photos.id', 'photos_tags.photo_id')
							->where('photos.owner_id', $user->id)
					)
			)
			->select('photo_id')
			->pluck('photo_id')
			->toArray();

		$existing_photo_ids = DB::table('photos_tags')
			->where('tag_id', $into->id)
			->whereIn('photo_id', $source_photo_ids)
			->select('photo_id')
			->pluck('photo_id')
			->toArray();

		// Those are the photos we need to add to the destination tag.
		$photo_ids_to_add = array_diff($source_photo_ids, $existing_photo_ids);

		DB::beginTransaction();
		try {
			if (count($photo_ids_to_add) > 0) {
				$insert_data = array_map(function ($photo_id) use ($into) {
					return [
						'photo_id' => $photo_id,
						'tag_id' => $into->id,
					];
				}, $photo_ids_to_add);
				DB::table('photos_tags')->insert($insert_data);
			}

			DB::table('photos_tags')
				->where('tag_id', $source->id)
				->whereIn('photo_id', $source_photo_ids) // Only the photos associated with the source tag and owned by the user
				->delete();

			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			throw $e;
		}

		// Cleanup unused tags after merging
		// This will remove the source tag if it has no more relationships.
		$this->cleanupUnusedTags();
	}
}
