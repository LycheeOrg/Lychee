<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Nsfw;

use App\Http\Requests\BaseApiRequest;

/**
 * Request for receiving NSFW detection results from the classification service.
 * Authentication is exclusively via X-API-Key header; no user session required.
 *
 * POST /api/v2/NsfwDetection/results
 */
class NsfwDetectionResultsRequest extends BaseApiRequest
{
	private string $photo_id;
	private string $status;
	private bool $should_block = false;
	private bool $should_review = false;
	private bool $is_sensitive = false;
	/** @var array<array-key,mixed> */
	private array $block_detected = [];
	/** @var array<array-key,mixed> */
	private array $review_detected = [];
	/** @var array<array-key,mixed> */
	private array $sensitive_detected = [];
	private ?string $error_code = null;
	private ?string $message = null;

	public function authorize(): bool
	{
		$expected_key = config('features.ai-vision-service.nsfw-api-key', '');
		$provided_key = $this->header('X-API-Key', '');

		return $expected_key !== '' && $provided_key === $expected_key;
	}

	public function rules(): array
	{
		return [
			'photo_id' => 'required|string',
			'status' => 'required|string|in:success,error',
			'should_block' => 'sometimes|boolean',
			'should_review' => 'sometimes|boolean',
			'is_sensitive' => 'sometimes|boolean',
			'all_detected' => 'sometimes|array',
			'all_detected.*.label' => 'required_with:all_detected|string',
			'all_detected.*.confidence' => 'required_with:all_detected|numeric',
			'all_detected.*.bbox' => 'required_with:all_detected|array',
			'all_detected.*.bbox.x' => 'required_with:all_detected|integer',
			'all_detected.*.bbox.y' => 'required_with:all_detected|integer',
			'all_detected.*.bbox.width' => 'required_with:all_detected|integer',
			'all_detected.*.bbox.height' => 'required_with:all_detected|integer',
			'all_detected.*.area_pixels' => 'sometimes|integer',
			'all_detected.*.area_ratio' => 'sometimes|numeric',
			'block_detected' => 'sometimes|array',
			'block_detected.*.label' => 'required_with:block_detected|string',
			'block_detected.*.confidence' => 'required_with:block_detected|numeric',
			'block_detected.*.bbox' => 'required_with:block_detected|array',
			'block_detected.*.bbox.x' => 'required_with:block_detected|integer',
			'block_detected.*.bbox.y' => 'required_with:block_detected|integer',
			'block_detected.*.bbox.width' => 'required_with:block_detected|integer',
			'block_detected.*.bbox.height' => 'required_with:block_detected|integer',
			'block_detected.*.area_pixels' => 'sometimes|integer',
			'block_detected.*.area_ratio' => 'sometimes|numeric',
			'review_detected' => 'sometimes|array',
			'review_detected.*.label' => 'required_with:review_detected|string',
			'review_detected.*.confidence' => 'required_with:review_detected|numeric',
			'review_detected.*.bbox' => 'required_with:review_detected|array',
			'review_detected.*.bbox.x' => 'required_with:review_detected|integer',
			'review_detected.*.bbox.y' => 'required_with:review_detected|integer',
			'review_detected.*.bbox.width' => 'required_with:review_detected|integer',
			'review_detected.*.bbox.height' => 'required_with:review_detected|integer',
			'review_detected.*.area_pixels' => 'sometimes|integer',
			'review_detected.*.area_ratio' => 'sometimes|numeric',
			'sensitive_detected' => 'sometimes|array',
			'sensitive_detected.*.label' => 'required_with:sensitive_detected|string',
			'sensitive_detected.*.confidence' => 'required_with:sensitive_detected|numeric',
			'sensitive_detected.*.bbox' => 'required_with:sensitive_detected|array',
			'sensitive_detected.*.bbox.x' => 'required_with:sensitive_detected|integer',
			'sensitive_detected.*.bbox.y' => 'required_with:sensitive_detected|integer',
			'sensitive_detected.*.bbox.width' => 'required_with:sensitive_detected|integer',
			'sensitive_detected.*.bbox.height' => 'required_with:sensitive_detected|integer',
			'sensitive_detected.*.area_pixels' => 'sometimes|integer',
			'sensitive_detected.*.area_ratio' => 'sometimes|numeric',
			'error_code' => 'sometimes|string',
			'message' => 'sometimes|string',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_id = $values['photo_id'];
		$this->status = $values['status'];
		$this->should_block = ($values['should_block'] ?? false) === true;
		$this->should_review = ($values['should_review'] ?? false) === true;
		$this->is_sensitive = ($values['is_sensitive'] ?? false) === true;
		$this->block_detected = $values['block_detected'] ?? [];
		$this->review_detected = $values['review_detected'] ?? [];
		$this->sensitive_detected = $values['sensitive_detected'] ?? [];
		$this->error_code = $values['error_code'] ?? null;
		$this->message = $values['message'] ?? null;
	}

	public function photoId(): string
	{
		return $this->photo_id;
	}

	public function status(): string
	{
		return $this->status;
	}

	public function shouldBlock(): bool
	{
		return $this->should_block;
	}

	public function shouldReview(): bool
	{
		return $this->should_review;
	}

	public function isSensitive(): bool
	{
		return $this->is_sensitive;
	}

	/** @return array<array-key,mixed> */
	public function blockDetected(): array
	{
		return $this->block_detected;
	}

	/** @return array<array-key,mixed> */
	public function reviewDetected(): array
	{
		return $this->review_detected;
	}

	/** @return array<array-key,mixed> */
	public function sensitiveDetected(): array
	{
		return $this->sensitive_detected;
	}

	public function errorCode(): ?string
	{
		return $this->error_code;
	}

	public function errorMessage(): ?string
	{
		return $this->message;
	}
}
