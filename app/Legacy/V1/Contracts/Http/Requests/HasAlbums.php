<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Contracts\Http\Requests;

use Illuminate\Support\Collection;

/**
 * @template T of \App\Contracts\Models\AbstractAlbum
 */
interface HasAlbums
{
	/**
	 * @return Collection<int,T>
	 */
	public function albums(): Collection;
}
