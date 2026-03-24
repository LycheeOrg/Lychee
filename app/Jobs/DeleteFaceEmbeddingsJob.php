<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Notifies the AI Vision Python service to delete stored embeddings for the
 * given face IDs.
 */
class DeleteFaceEmbeddingsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	/** @var list<string> */
	public array $face_ids;

	/**
	 * @param list<string> $face_ids
	 */
	public function __construct(array $face_ids)
	{
		$this->face_ids = $face_ids;
	}

	public function handle(): void
	{
		if ($this->face_ids === []) {
			return;
		}

		$service_url = config('features.ai-vision.face-url', '');
		$api_key = config('features.ai-vision.face-api-key', '');

		if ($service_url === '') {
			Log::warning('DeleteFaceEmbeddingsJob: AI Vision service not configured.');

			return;
		}

		try {
			$response = Http::withHeaders(['X-API-Key' => $api_key])
				->delete($service_url . '/embeddings', ['face_ids' => $this->face_ids]);

			$ok = $response->successful();
			if (!$ok) {
				Log::warning('DeleteFaceEmbeddingsJob: /embeddings DELETE returned HTTP ' . $response->status() . '.', ['face_ids' => $this->face_ids]);
			}
		} catch (\Exception $e) {
			Log::warning('DeleteFaceEmbeddingsJob: request failed: ' . $e->getMessage(), ['face_ids' => $this->face_ids]);
		}
	}
}
