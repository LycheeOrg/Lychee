<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Track;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * DO-055-02: `file_name`/`disk`/`is_primary` are never serialized (internal
 * storage details), mirroring {@link SizeVariantResource}'s convention.
 */
#[TypeScript()]
class TrackResource extends Data
{
	public int $id;
	public string $name;
	public string $url;

	public function __construct(Track $track)
	{
		$this->id = $track->id;
		$this->name = $track->name;
		$this->url = $track->url;
	}
}
