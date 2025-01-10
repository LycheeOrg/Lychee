<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Sharing;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ListedAlbumsResource extends Data
{
	public string $id;
	public string $title;

	/**
	 * @param object{id:string,title:string} $albumListed
	 */
	public function __construct(object $albumListed)
	{
		$this->id = $albumListed->id;
		$this->title = $albumListed->title;
	}
}
