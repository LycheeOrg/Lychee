<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Admin;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Response for the "select all matching IDs" endpoint.
 *
 * ids: array of album IDs (up to 1 000).
 * capped: true when the total exceeded 1 000 and the list was truncated.
 */
#[TypeScript()]
class BulkAlbumIdsResource extends Data
{
	/** @var string[] */
	public array $ids;
	public bool $capped;

	/**
	 * @param string[] $ids
	 */
	public function __construct(array $ids, bool $capped)
	{
		$this->ids = $ids;
		$this->capped = $capped;
	}
}
