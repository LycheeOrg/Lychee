<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Resources\Collections;

use App\Legacy\V1\Resources\Models\AlbumResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Provide stronger typechecking for Album collections.
 */
final class AlbumCollectionResource extends ResourceCollection
{
	/**
	 * The resource that this resource collects.
	 *
	 * @var string
	 */
	public $collects = AlbumResource::class;
}
