<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\NsfwDetection;
use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PhotoNsfwDetectionsResource extends Data
{
	/** @var NsfwDetectionResource[] */
	#[LiteralTypeScriptType('App.Http.Resources.Models.NsfwDetectionResource[]')]
	public array $detections = [];
	public int $image_width;
	public int $image_height;

	public function __construct(Photo $photo)
	{
		$original = $photo->size_variants->getOriginal();
		$this->image_width = $original?->width ?? 1;
		$this->image_height = $original?->height ?? 1;

		foreach ($photo->nsfwDetections as $detection) {
			/** @var NsfwDetection $detection */
			$this->detections[] = new NsfwDetectionResource($detection);
		}
	}
}
