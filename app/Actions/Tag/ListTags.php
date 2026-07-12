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
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * We list all the tags with their photo and album counts (Feature 050).
 *
 * We also make sure to only return the tags which are associated with photos
 * or albums of the current user (unless it's the admin).
 */
class ListTags
{
	/**
	 * Returns the list of all tags with their photo and album counts.
	 *
	 * @return TagsResource
	 */
	public function do(): TagsResource
	{
		/** @var User $user */
		$user = Auth::user();
		$is_admin = $user->may_administrate;

		/** @var Collection<int,object{id:int,name:string,num_photos:int,num_albums:int}> $tags */
		$tags = DB::table('tags')
			->selectRaw(
				'tags.id AS id, tags.name AS name, (SELECT COUNT(*) FROM photos_tags '
					. ($is_admin ? '' : 'INNER JOIN photos ON photos.id = photos_tags.photo_id ')
					. 'WHERE photos_tags.tag_id = tags.id'
					. ($is_admin ? '' : ' AND photos.owner_id = ?')
					. ') AS num_photos',
				$is_admin ? [] : [$user->id]
			)
			->selectRaw(
				'(SELECT COUNT(*) FROM albums_tags '
					. ($is_admin ? '' : 'INNER JOIN base_albums ON base_albums.id = albums_tags.album_id ')
					. 'WHERE albums_tags.tag_id = tags.id'
					. ($is_admin ? '' : ' AND base_albums.owner_id = ?')
					. ') AS num_albums',
				$is_admin ? [] : [$user->id]
			)
			// Exclude tags with no accessible photo and no accessible album => this
			// makes sure we do not leak tags from other users (non-admins) nor
			// fully-orphaned tags (admin).
			->where(function ($query) use ($user, $is_admin): void {
				$query->whereExists(function (Builder $sub) use ($user, $is_admin): void {
					$sub->selectRaw(1)
						->from('photos_tags')
						->whereColumn('photos_tags.tag_id', 'tags.id')
						->when(!$is_admin, fn ($q) => $q
							->join('photos', 'photos.id', '=', 'photos_tags.photo_id')
							->where('photos.owner_id', $user->id));
				})->orWhereExists(function (Builder $sub) use ($user, $is_admin): void {
					$sub->selectRaw(1)
						->from('albums_tags')
						->whereColumn('albums_tags.tag_id', 'tags.id')
						->when(!$is_admin, fn ($q) => $q
							->join('base_albums', 'base_albums.id', '=', 'albums_tags.album_id')
							->where('base_albums.owner_id', $user->id));
				});
			})
			->orderBy('tags.name')
			->get();

		return new TagsResource($tags->map(fn ($tag) => new TagResource(
			id: $tag->id,
			name: $tag->name,
			num_photos: $tag->num_photos,
			num_albums: $tag->num_albums,
		)));
	}
}
