<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Tag;

use App\Http\Resources\Tags\TagResource;
use App\Http\Resources\Tags\TagsResource;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * We list all the tags with their photo counts.
 *
 * We also make sure to only return the taags which are associated with photos of the current user (unless it's the admin).
 */
class ListTags
{
	/**
	 * Returns the list of all tags with their photo counts.
	 *
	 * @return TagsResource
	 */
	public function do(): TagsResource
	{
		/** @var User $user */
		$user = Auth::user();

		/** @var Collection<int,object{id:int,name:string,num:int}> $tags */
		$tags = DB::table('tags')
			->leftJoin('photos_tags', 'tags.id', '=', 'photos_tags.tag_id')
			->when(
				$user->may_administrate === false,
				fn ($q) => $q
					->leftJoin('photos', 'photos.id', '=', 'photos_tags.photo_id')
					->where('photos.owner_id', $user->id)
			)
			->select(['tags.id', 'tags.name', DB::raw('COUNT(photos_tags.photo_id) AS num')])
			->groupBy(['tags.id', 'tags.name'])
			->orderBy('tags.name')
			->havingRaw('COUNT(photos_tags.photo_id) > 0') // Exclude tags with no photos => this makes sure we do not leak tags from other users.
			// Here we can not use `->having('num', '>', 0)` because the aliasing in Postgresql is done AFTER the `HAVING` clause...
			->get();

		return new TagsResource($tags->map(fn ($tag) => new TagResource(
			id: $tag->id,
			name: $tag->name,
			num: $tag->num
		)));
	}
}
