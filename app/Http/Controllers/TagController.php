<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Exceptions\TagAlreadyExistException;
use App\Http\Requests\Tags\DeleteTagRequest;
use App\Http\Requests\Tags\EditTagRequest;
use App\Http\Requests\Tags\GetTagRequest;
use App\Http\Requests\Tags\ListTagRequest;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Tags\TagResource;
use App\Http\Resources\Tags\TagWithPhotosResource;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\Tag;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
	/**
	 * Returns the list of all tags with their photo counts.
	 *
	 * @return Collection<int,TagResource>
	 */
	public function list(ListTagRequest $request): Collection
	{
		/** @var Collection<int,object{name:string,num:int}> $tags */
		$tags = DB::table('tags')
			->select(['tags.id', 'tags.name', DB::raw('COUNT(photos_tags.photo_id) AS num')])
			->leftJoin('photos_tags', 'tags.id', '=', 'photos_tags.tag_id')
			->groupBy(['tags.id', 'tags.name'])
			->orderBy('tags.name')
			->get();

		return $tags->map(fn ($tag) => new TagResource(
			name: $tag->name,
			num: $tag->num
		));
	}

	/**
	 * Returns a tag with its associated photos.
	 *
	 * @return TagWithPhotosResource
	 */
	public function get(GetTagRequest $request, PhotoQueryPolicy $photo_query_policy): TagWithPhotosResource
	{
		$tag = $request->tag();

		// Start with a base Photo query and include the tag join
		$base_query = Photo::query()
			->with(['size_variants', 'statistics', 'palette', 'tags'])
			->whereHas('tags', function ($query) use ($tag): void {
				$query->where('tags.id', $tag->id);
			});

		// Apply searchability filter
		$photos_query = $photo_query_policy->applySearchabilityFilter(
			query: $base_query,
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_smart_albums')
		);

		/** @var \Illuminate\Support\Collection<int,Photo> $photos */
		$photos = $photos_query->get();

		$photo_resources = $photos->map(fn (Photo $photo) => new PhotoResource($photo, null));

		return new TagWithPhotosResource(
			name: $tag->name,
			photos: $photo_resources,
		);
	}

	public function edit(EditTagRequest $request): void
	{
		$tag = $request->tag();

		if (Tag::where('name', $request->name())->exists()) {
			throw new TagAlreadyExistException();
		}

		// Update the tag name
		DB::table('tags')
			->where('id', $tag->id)
			->update(['name' => $request->name()]);
	}

	public function delete(DeleteTagRequest $request): void
	{
		$tags = $request->tags();

		if (count($tags) === 0) {
			return;
		}

		// First delete all the relations between the selected tags and the photos
		DB::table('photos_tags')
			->whereIn('tag_id', fn ($q) => $q->select('id')
					->from('tags')
					->whereIn('name', $tags)
			)
			->delete();

		// Then delete the tags themselves
		DB::table('tags')
			->whereIn('name', $tags)
			->delete();
	}
}
