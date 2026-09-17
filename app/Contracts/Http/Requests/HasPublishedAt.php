<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use Illuminate\Support\Carbon;

interface HasPublishedAt
{
	public function publishedAt(): ?Carbon;

	/**
	 * Whether the `published_at` key was present in the request payload at
	 * all. A field can be present-but-null (clear the value) or absent
	 * (leave the existing value untouched) - see {@see self::publishedAt()}.
	 */
	public function publishedAtProvided(): bool;
}
