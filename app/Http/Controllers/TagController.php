<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Http\Requests\Tags\ListTagRequest;
use App\Http\Resources\Tags\TagResource;
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
}
