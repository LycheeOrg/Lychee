<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\NsfwDetection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class NsfwDetectionResource extends Data
{
	public int $id;
	public string $photo_id;
	public string $label;
	public float $confidence;
	public int $bbox_x;
	public int $bbox_y;
	public int $bbox_width;
	public int $bbox_height;
	public bool $is_block;
	public bool $is_review;
	public bool $is_sensitive;

	public function __construct(NsfwDetection $detection)
	{
		$this->id = $detection->id;
		$this->photo_id = $detection->photo_id;
		$this->label = $detection->label->value;
		$this->confidence = $detection->confidence;
		$this->bbox_x = $detection->bbox_x;
		$this->bbox_y = $detection->bbox_y;
		$this->bbox_width = $detection->bbox_width;
		$this->bbox_height = $detection->bbox_height;
		$this->is_block = $detection->is_block;
		$this->is_review = $detection->is_review;
		$this->is_sensitive = $detection->is_sensitive;
	}
}
