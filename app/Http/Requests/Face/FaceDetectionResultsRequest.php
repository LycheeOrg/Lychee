<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;

/**
 * Request for receiving face detection results from the Python AI Vision service.
 * Authentication is exclusively via X-API-Key header; no user session required.
 *
 * POST /api/v2/FaceDetection/results
 */
class FaceDetectionResultsRequest extends BaseApiRequest
{
	private string $photo_id;
	private string $status;
	/** @var array<array{x:float,y:float,width:float,height:float,confidence:float,laplacian_variance:float,embedding_id:string,crop?:string,suggestions?:array<array{lychee_face_id:string,confidence:float}>}> */
	private array $faces = [];
	private ?string $error_code = null;
	private ?string $message = null;

	/**
	 * {@inheritDoc}
	 *
	 * Validates the X-API-Key header against the configured secret.
	 */
	public function authorize(): bool
	{
		$expected_key = config('features.ai-vision-service.face-api-key', '');
		$provided_key = $this->header('X-API-Key', '');

		return $expected_key !== '' && $provided_key === $expected_key;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		// TODO: Remove the photo,id check. The AI Vision service may send results for photos that have been deleted in the meantime, and we want to be able to handle that gracefully.
		return [
			'photo_id' => 'required|string|exists:photos,id',
			'status' => 'required|string|in:success,error',
			'faces' => 'sometimes|array',
			'faces.*.x' => 'required_with:faces|numeric',
			'faces.*.y' => 'required_with:faces|numeric',
			'faces.*.width' => 'required_with:faces|numeric',
			'faces.*.height' => 'required_with:faces|numeric',
			'faces.*.confidence' => 'required_with:faces|numeric',
			'faces.*.laplacian_variance' => 'required_with:faces|numeric',
			'faces.*.embedding_id' => 'required_with:faces|string',
			'faces.*.crop' => 'sometimes|string',
			'faces.*.suggestions' => 'sometimes|array',
			'faces.*.suggestions.*.lychee_face_id' => 'required|string',
			'faces.*.suggestions.*.confidence' => 'required|numeric',
			'error_code' => 'sometimes|string',
			'message' => 'sometimes|string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_id = $values['photo_id'];
		$this->status = $values['status'];
		$this->faces = $values['faces'] ?? [];
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

	/**
	 * @return array<array{x:float,y:float,width:float,height:float,confidence:float,embedding_id:string,crop?:string,suggestions?:array<array{lychee_face_id:string,confidence:float}>}>
	 */
	public function faces(): array
	{
		return $this->faces;
	}

	public function errorCode(): ?string
	{
		return $this->error_code;
	}

	public function message(): ?string
	{
		return $this->message;
	}
}
