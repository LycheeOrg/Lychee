<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\Face;
use App\Models\FaceSuggestion;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class FaceResource extends Data
{
	public string $id;
	public string $photo_id;
	public ?string $person_id;
	public float $x;
	public float $y;
	public float $width;
	public float $height;
	public float $confidence;
	public bool $is_dismissed;
	public ?string $crop_url;
	public ?string $person_name;
	/** @var FaceSuggestionResource[] */
	public array $suggestions;

	public function __construct(Face $face)
	{
		$this->id = $face->id;
		$this->photo_id = $face->photo_id;
		$this->person_id = $face->person_id;
		$this->x = $face->x;
		$this->y = $face->y;
		$this->width = $face->width;
		$this->height = $face->height;
		$this->confidence = $face->confidence;
		$this->is_dismissed = $face->is_dismissed;
		$this->crop_url = $face->crop_url;
		$this->person_name = $face->person?->name;
		$this->suggestions = $face->suggestions
			->map(fn (FaceSuggestion $s) => new FaceSuggestionResource($s))
			->values()
			->all();
	}

	public static function fromModel(Face $face): self
	{
		return new self($face);
	}
}
