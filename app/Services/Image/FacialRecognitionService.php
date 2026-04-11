<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Image;

use App\Exceptions\ExternalComponentMissingException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use function Safe\file_get_contents;

/**
 * Centralized service for all HTTP requests to the AI Vision facial recognition service.
 */
class FacialRecognitionService
{
	private string $service_url;
	private string $api_key;

	public function __construct()
	{
		$this->service_url = config('features.ai-vision-service.face-url', '');
		$this->api_key = config('features.ai-vision-service.face-api-key', '');
	}

	/**
	 * Check if the service is configured.
	 */
	public function isConfigured(): bool
	{
		return $this->service_url !== '';
	}

	/**
	 * Match a selfie image against stored face embeddings.
	 *
	 * @param string $file_path The path to the selfie image file
	 * @param string $file_name The original filename
	 *
	 * @return array{matches: array<array{lychee_face_id: string, confidence: float}>}|null
	 *
	 * @throws \Exception When the HTTP request fails
	 */
	public function matchSelfie(string $file_path, string $file_name): ?array
	{
		if (!$this->isConfigured()) {
			Log::warning('FacialRecognitionService: matchSelfie called but service is not configured.');

			return null;
		}

		$response = Http::withHeaders(['X-API-Key' => $this->api_key])
			->attach('image', file_get_contents($file_path), $file_name)
			->post($this->service_url . '/match');

		return $response->successful() ? $response->json() : null;
	}

	/**
	 * Detect faces in a photo.
	 *
	 * @param string $photo_id   The photo ID
	 * @param string $photo_path The path to the photo
	 *
	 * @return Response
	 *
	 * @throws \Exception When the HTTP request fails
	 */
	public function detectFaces(string $photo_id, string $photo_path): Response
	{
		if (!$this->isConfigured()) {
			throw new ExternalComponentMissingException('AI Vision service is not configured.');
		}

		$data = [
			'photo_id' => $photo_id,
			'photo_path' => $photo_path,
		];

		return Http::withHeaders(['X-API-Key' => $this->api_key])
			->post($this->service_url . '/detect', $data);
	}

	/**
	 * Delete face embeddings from the AI Vision service.
	 *
	 * @param list<string> $face_ids The face IDs to delete
	 *
	 * @return Response
	 *
	 * @throws \Exception When the HTTP request fails
	 */
	public function deleteEmbeddings(array $face_ids): Response
	{
		if (!$this->isConfigured()) {
			throw new ExternalComponentMissingException('AI Vision service is not configured.');
		}

		return Http::withHeaders(['X-API-Key' => $this->api_key])
			->delete($this->service_url . '/embeddings', ['face_ids' => $face_ids]);
	}

	/**
	 * Check the health status of the AI Vision service.
	 *
	 * @return array{status: string, model_loaded: bool, embedding_count: int}|null
	 *
	 * @throws \Exception When the HTTP request fails
	 */
	public function checkHealth(): ?array
	{
		if (!$this->isConfigured()) {
			Log::warning('FacialRecognitionService: checkHealth called but service is not configured.');

			return null;
		}

		$response = Http::withHeaders(['X-API-Key' => $this->api_key])
			->get($this->service_url . '/health');

		return $response->successful() ? $response->json() : null;
	}

	/**
	 * Export all face embeddings with metadata for synchronization.
	 *
	 * @return array{count: int, embeddings: array<array{lychee_face_id: string, photo_id: string, laplacian_variance: float, crop_path: string}>}|null
	 *
	 * @throws \Exception When the HTTP request fails
	 */
	public function syncFaceEmbeddings(): ?array
	{
		if (!$this->isConfigured()) {
			Log::warning('FacialRecognitionService: syncFaceEmbeddings called but service is not configured.');

			return null;
		}

		$response = Http::withHeaders(['X-API-Key' => $this->api_key])
			->get($this->service_url . '/embeddings/export');

		return $response->successful() ? $response->json() : null;
	}
}
