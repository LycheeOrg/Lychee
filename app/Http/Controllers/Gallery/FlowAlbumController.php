<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Albums\Flow;
use App\Http\Requests\Flow\FlowAlbumRequest;
use App\Http\Resources\Flow\FlowAlbumResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

class FlowAlbumController extends Controller
{
	/**
	 * Handle the incoming request.
	 *
	 * @param FlowAlbumRequest $request
	 */
	public function __invoke(FlowAlbumRequest $request, Flow $flow)
	{
		$pagination_limit = Configs::getValueAsInt('flow_max_items');
		$album_results = $flow->do()->paginate($pagination_limit);

		return FlowAlbumResource::fromData($album_results);
	}
}
