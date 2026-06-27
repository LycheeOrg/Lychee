<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Image;

use App\Enum\NsfwStatus;
use App\Exceptions\ExternalComponentMissingException;
use App\Jobs\DispatchNsfwScanJob;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NsfwDetectionService
{
	private string $service_url;
	private string $api_key;

	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
		$this->service_url = config('features.ai-vision-service.nsfw-url', '');
		$this->api_key = config('features.ai-vision-service.nsfw-api-key', '');
	}

	public function isConfigured(): bool
	{
		return $this->service_url !== '';
	}

	/**
	 * @param int $timeout Request timeout in seconds
	 *
	 * @return Response
	 *
	 * @throws ExternalComponentMissingException
	 */
	public function checkHealthRaw(int $timeout = 5): Response
	{
		if (!$this->isConfigured()) {
			throw new ExternalComponentMissingException('NSFW classification service is not configured.');
		}

		return Http::withHeaders(['X-API-Key' => $this->api_key])
			->timeout($timeout)
			->get($this->service_url . '/api/nsfw/health');
	}

	/**
	 * @return array{status: string}|null
	 */
	public function checkHealth(): ?array
	{
		if (!$this->isConfigured()) {
			Log::warning('NsfwDetectionService: checkHealth called but service is not configured.');

			return null;
		}

		try {
			$response = $this->checkHealthRaw();

			return $response->successful() ? $response->json() : null;
		} catch (\Exception) {
			return null;
		}
	}

	/**
	 * @param int $timeout Request timeout in seconds
	 *
	 * @return Response
	 *
	 * @throws ExternalComponentMissingException
	 */
	public function getConfigurationRaw(int $timeout = 5): Response
	{
		if (!$this->isConfigured()) {
			throw new ExternalComponentMissingException('NSFW classification service is not configured.');
		}

		return Http::withHeaders(['X-API-Key' => $this->api_key])
			->timeout($timeout)
			->get($this->service_url . '/api/nsfw/config');
	}

	/**
	 * @return array<string,string>|null
	 */
	public function getConfiguration(): ?array
	{
		if (!$this->isConfigured()) {
			Log::warning('NsfwDetectionService: getConfiguration called but service is not configured.');

			return null;
		}

		try {
			$response = $this->getConfigurationRaw();
			if (!$response->successful()) {
				return null;
			}

			$payload = $response->json();
			if (!is_array($payload) || !isset($payload['config']) || !is_array($payload['config'])) {
				return null;
			}

			$config = [];
			foreach ($payload['config'] as $key => $value) {
				if (is_string($key)) {
					$config[$key] = (string) $value;
				}
			}

			return $config;
		} catch (\Exception) {
			return null;
		}
	}

	/**
	 * Dispatch a single photo for NSFW scanning.
	 */
	public function dispatchPhoto(string $photo_id, ?string $photo_path): Response
	{
		if (!$this->isConfigured()) {
			throw new ExternalComponentMissingException('NSFW classification service is not configured.');
		}

		$data = [
			'photo_id' => $photo_id,
			'photo_path' => $photo_path,
		];

		$preset = $this->config_manager->getValueAsString('ai_vision_nsfw_preset');
		if ($preset !== '' && $preset !== 'default') {
			$data['preset'] = $preset;
		}

		return Http::withHeaders(['X-API-Key' => $this->api_key])
			->post($this->service_url . '/api/nsfw/detect', $data);
	}

	/**
	 * Dispatch unscanned photos for NSFW scanning.
	 *
	 * @return int Number of photos dispatched
	 */
	public function dispatchUnscannedPhotos(?string $album_id, bool $force = false): int
	{
		$query = Photo::query()
			->select('id')
			->where('type', 'like', 'image/%');

		if ($album_id !== null) {
			$query->whereHas('albums', fn ($q) => $q->where('albums.id', $album_id));
		}

		if (!$force) {
			$query->where(function ($q): void {
				$q->whereNull('nsfw_status')
					->orWhere('nsfw_status', NsfwStatus::FAILED);
			});
		}

		$count = 0;
		$query->chunkById(100, function ($photos) use (&$count): void {
			foreach ($photos as $photo) {
				DispatchNsfwScanJob::dispatch($photo->id);
				$count++;
			}
		});

		Log::info("NsfwDetectionService: dispatched {$count} photos for NSFW scanning.");

		return $count;
	}
}
