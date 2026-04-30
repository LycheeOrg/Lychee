<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Models\FaceSuggestion;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class FaceSuggestionResource extends Data
{
	public string $suggested_face_id;
	public ?string $crop_url;
	public ?string $person_name;
	public float $confidence;

	public function __construct(FaceSuggestion $suggestion)
	{
		$this->suggested_face_id = $suggestion->suggested_face_id;
		$this->crop_url = $suggestion->suggestedFace->crop_url ?? null;
		$this->person_name = $suggestion->suggestedFace->person?->name ?? null;
		$this->confidence = $suggestion->confidence;
	}
}
