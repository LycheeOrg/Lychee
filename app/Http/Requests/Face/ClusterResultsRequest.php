<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Log;

/**
 * Request for receiving face clustering results from the Python AI Vision service.
 * Authentication is exclusively via X-API-Key header; no user session required.
 *
 * POST /api/v2/FaceDetection/cluster-results
 */
class ClusterResultsRequest extends BaseApiRequest
{
	/** @var array<array{face_id:string,cluster_label:int}> */
	private array $labels = [];

	/** @var array<array{face_id:string,suggested_face_id:string,confidence:float}> */
	private array $suggestions = [];

	public function authorize(): bool
	{
		$expected_key = config('features.ai-vision-service.face-api-key', '');
		$provided_key = $this->header('X-API-Key', '');

		return $expected_key !== '' && $provided_key === $expected_key;
	}

	public function rules(): array
	{
		return [
			'labels' => 'required|array',
			'labels.*.face_id' => 'required|string',
			'labels.*.cluster_label' => 'required|integer',
			'suggestions' => 'sometimes|array',
			'suggestions.*.face_id' => 'required|string',
			'suggestions.*.suggested_face_id' => 'required|string',
			'suggestions.*.confidence' => 'required|numeric',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->labels = $values['labels'] ?? [];
		$this->suggestions = $values['suggestions'] ?? [];
		Log::warning('Received face clustering results with ' . count($this->labels) . ' labels and ' . count($this->suggestions) . ' suggestions.',
			[
				'labels' => $this->labels,
				'suggestions' => $this->suggestions,
			]);
	}

	/**
	 * @return array<array{face_id:string,cluster_label:int}>
	 */
	public function labels(): array
	{
		return $this->labels;
	}

	/**
	 * @return array<array{face_id:string,suggested_face_id:string,confidence:float}>
	 */
	public function suggestions(): array
	{
		return $this->suggestions;
	}
}
