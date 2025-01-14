<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Extensions\Thumb;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ThumbResource extends Data
{
	public string $id;
	public string $type;
	public ?string $thumb;
	public ?string $thumb2x;
	public ?string $placeholder;

	public function __construct(string $id, string $type, string $thumbUrl, ?string $thumb2xUrl = null, ?string $placeholderUrl = null)
	{
		$this->id = $id;
		$this->type = $type;
		$this->thumb = $thumbUrl;
		$this->thumb2x = $thumb2xUrl;
		$this->placeholder = $placeholderUrl;
	}

	/**
	 * Produce a thumb resource from a Thumb object if existing.
	 *
	 * @param Thumb|null $thumb
	 *
	 * @return ThumbResource|null
	 */
	public static function fromModel(?Thumb $thumb): ?self
	{
		if ($thumb === null) {
			return null;
		}

		return new self($thumb->id, $thumb->type, $thumb->thumbUrl, $thumb->thumb2xUrl, $thumb->placeholderUrl);
	}
}
