<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\FaceScanStatus;
use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches a single photo to the AI Vision Python service for face detection.
 * The Python service calls back via POST /api/v2/FaceDetection/results.
 */
class DispatchFaceScanJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public string $photo_id;

	public function __construct(string $photo_id)
	{
		$this->photo_id = $photo_id;
	}

	/**
	 * Execute the job: send the photo to the AI Vision service.
	 */
	public function handle(): void
	{
		$service_url = config('features.ai-vision.face-url', '');
		$api_key = config('features.ai-vision.face-api-key', '');

		$photo = Photo::with('size_variants')->find($this->photo_id);

		if ($photo === null) {
			Log::warning("DispatchFaceScanJob: photo {$this->photo_id} not found, skipping.");

			return;
		}

		if ($service_url === '') {
			Log::warning("DispatchFaceScanJob: AI Vision service not configured, marking photo {$this->photo_id} as failed.");
			$photo->face_scan_status = FaceScanStatus::FAILED;
			$photo->save();

			return;
		}

		$original = $photo->size_variants->getOriginal();

		if ($original === null) {
			Log::warning("DispatchFaceScanJob: no original size variant for photo {$this->photo_id}, marking as failed.");
			$photo->face_scan_status = FaceScanStatus::FAILED;
			$photo->save();

			return;
		}

		try {
			$data = [
				'photo_id' => $this->photo_id,
				'photo_path' => $original->short_path, // We just send short path. Not that this means that we do not support s3 for facial recognition.
			];
			$response = Http::withHeaders(['X-API-Key' => $api_key])
				->post($service_url . '/detect', $data);

			if (!$response->successful()) {
				Log::warning("DispatchFaceScanJob: /detect returned HTTP {$response->status()} for photo {$this->photo_id}.", ['data' => $data, 'response' => $response->json()]);
				$photo->face_scan_status = FaceScanStatus::FAILED;
				$photo->save();
			}
		} catch (\Exception $e) {
			Log::warning("DispatchFaceScanJob: /detect request failed for photo {$this->photo_id}: " . $e->getMessage());
			$photo->face_scan_status = FaceScanStatus::FAILED;
			$photo->save();
		}
	}
}
