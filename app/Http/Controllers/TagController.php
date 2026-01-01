<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Tag\DeleteTag;
use App\Actions\Tag\EditTag;
use App\Actions\Tag\GetTagWithPhotos;
use App\Actions\Tag\ListTags;
use App\Actions\Tag\MergeTag;
use App\Http\Requests\Tags\DeleteTagRequest;
use App\Http\Requests\Tags\EditTagRequest;
use App\Http\Requests\Tags\GetTagRequest;
use App\Http\Requests\Tags\ListTagRequest;
use App\Http\Requests\Tags\MergeTagRequest;
use App\Http\Resources\Tags\TagsResource;
use App\Http\Resources\Tags\TagWithPhotosResource;
use Illuminate\Routing\Controller;

class TagController extends Controller
{
	public function list(ListTagRequest $request, ListTags $list_tags): TagsResource
	{
		return $list_tags->do();
	}

	public function get(GetTagRequest $request, GetTagWithPhotos $get_tag_with_photos): TagWithPhotosResource
	{
		$tag = $request->tag();

		return $get_tag_with_photos->do($tag);
	}

	public function edit(EditTagRequest $request, EditTag $edit_tag): void
	{
		$tag = $request->tag();
		$name = $request->name();

		$edit_tag->do($tag, $name);
	}

	public function delete(DeleteTagRequest $request, DeleteTag $delete_tag): void
	{
		$tags = $request->tags;

		if (count($tags) === 0) {
			return;
		}

		$delete_tag->do($tags);
	}

	public function merge(MergeTagRequest $request, MergeTag $merge_tag): void
	{
		$merge_tag->do($request->tag(), $request->destinationTag());
	}
}
