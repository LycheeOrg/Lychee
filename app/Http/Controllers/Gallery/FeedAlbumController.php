<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Albums\Feed;
use App\Http\Requests\Feed\FeedAlbumRequest;
use App\Http\Resources\Feed\FeedAlbumResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

class FeedAlbumController extends Controller
{
	/**
	 * Handle the incoming request.
	 *
	 * @param FeedAlbumRequest $request
	 */
	public function __invoke(FeedAlbumRequest $request, Feed $feed)
	{
		$pagination_limit = 20;
		Configs::getValueAsInt('feed_max_items');
		$album_results = $feed->do()->paginate($pagination_limit);

		return FeedAlbumResource::fromData($album_results);
	}
}
