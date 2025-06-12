<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Albums\Feed;
use App\Http\Requests\Feed\FeedRequest;
use App\Http\Resources\Feed\FeedResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

class FeedController extends Controller
{
	/**
	 * Handle the incoming request.
	 *
	 * @param FeedRequest $request
	 */
	public function __invoke(FeedRequest $request, Feed $feed)
	{
		$pagination_limit = 20;
		Configs::getValueAsInt('feed_max_items');
		$album_results = $feed->do()->paginate($pagination_limit);

		return FeedResource::fromData($album_results);
	}
}
