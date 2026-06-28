<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Nsfw;

use App\DTO\Nsfw\NsfwDetectionResultsData;
use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

/**
 * Request for receiving NSFW detection results from the classification service.
 * Authentication is exclusively via X-API-Key header; no user session required.
 *
 * POST /api/v2/NsfwDetection/results
 */
class NsfwDetectionResultsRequest extends BaseApiRequest
{
	public NsfwDetectionResultsData $result;

	public function authorize(): bool
	{
		$expected_key = config('features.ai-vision-service.nsfw-api-key', '');
		$provided_key = $this->header('X-API-Key', '');

		return $expected_key !== '' && $provided_key === $expected_key;
	}

	public function rules(): array
	{
		return [
			'photo_id' => ['required', 'string'],
			'status' => ['required', 'string', 'in:success,error'],
			'should_block' => ['sometimes', 'boolean'],
			'should_review' => ['sometimes', 'boolean'],
			'is_sensitive' => ['sometimes', 'boolean'],
			'all_detected' => ['sometimes', 'array'],
			'all_detected.*.label' => ['required_with:all_detected', new Enum(NsfwDetectionResultsData::class)],
			'all_detected.*.confidence' => ['required_with:all_detected', 'numeric'],
			'all_detected.*.bbox' => ['required_with:all_detected', 'array'],
			'all_detected.*.bbox.x' => ['required_with:all_detected', 'integer'],
			'all_detected.*.bbox.y' => ['required_with:all_detected', 'integer'],
			'all_detected.*.bbox.width' => ['required_with:all_detected', 'integer'],
			'all_detected.*.bbox.height' => ['required_with:all_detected', 'integer'],
			'all_detected.*.area_pixels' => ['sometimes', 'integer'],
			'all_detected.*.area_ratio' => ['sometimes', 'numeric'],
			'block_detected' => ['sometimes', 'array'],
			'block_detected.*.label' => ['required_with:block_detected', 'string', new Enum(NsfwDetectionResultsData::class)],
			'block_detected.*.confidence' => ['required_with:block_detected', 'numeric'],
			'block_detected.*.bbox' => ['required_with:block_detected', 'array'],
			'block_detected.*.bbox.x' => ['required_with:block_detected', 'integer'],
			'block_detected.*.bbox.y' => ['required_with:block_detected', 'integer'],
			'block_detected.*.bbox.width' => ['required_with:block_detected', 'integer'],
			'block_detected.*.bbox.height' => ['required_with:block_detected', 'integer'],
			'block_detected.*.area_pixels' => ['sometimes', 'integer'],
			'block_detected.*.area_ratio' => ['sometimes', 'numeric'],
			'review_detected' => ['sometimes', 'array'],
			'review_detected.*.label' => ['required_with:review_detected', 'string', new Enum(NsfwDetectionResultsData::class)],
			'review_detected.*.confidence' => ['required_with:review_detected', 'numeric'],
			'review_detected.*.bbox' => ['required_with:review_detected', 'array'],
			'review_detected.*.bbox.x' => ['required_with:review_detected', 'integer'],
			'review_detected.*.bbox.y' => ['required_with:review_detected', 'integer'],
			'review_detected.*.bbox.width' => ['required_with:review_detected', 'integer'],
			'review_detected.*.bbox.height' => ['required_with:review_detected', 'integer'],
			'review_detected.*.area_pixels' => 'sometimes|integer',
			'review_detected.*.area_ratio' => 'sometimes|numeric',
			'sensitive_detected' => ['sometimes', 'array'],
			'sensitive_detected.*.label' => ['required_with:sensitive_detected', 'string', new Enum(NsfwDetectionResultsData::class)],
			'sensitive_detected.*.confidence' => ['required_with:sensitive_detected', 'numeric'],
			'sensitive_detected.*.bbox' => ['required_with:sensitive_detected', 'array'],
			'sensitive_detected.*.bbox.x' => ['required_with:sensitive_detected', 'integer'],
			'sensitive_detected.*.bbox.y' => ['required_with:sensitive_detected', 'integer'],
			'sensitive_detected.*.bbox.width' => ['required_with:sensitive_detected', 'integer'],
			'sensitive_detected.*.bbox.height' => ['required_with:sensitive_detected', 'integer'],
			'sensitive_detected.*.area_pixels' => ['sometimes', 'integer'],
			'sensitive_detected.*.area_ratio' => ['sometimes', 'numeric'],
			'error_code' => ['sometimes', 'string'],
			'message' => ['sometimes', 'string'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		Log::info('NsfwDetectionResultsRequest: processing validated values.', ['values' => $values]);
		$this->result = NsfwDetectionResultsData::from($values); // Validate and transform the data
	}
}
