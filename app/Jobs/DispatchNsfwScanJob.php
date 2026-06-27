<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\NsfwStatus;
use App\Models\Photo;
use App\Services\Image\NsfwDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchNsfwScanJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 3;
	public int $backoff = 10;

	public function __construct(
		public string $photo_id,
	) {
	}

	public function handle(NsfwDetectionService $service): void
	{
		$photo = Photo::with('size_variants')->find($this->photo_id);

		if ($photo === null) {
			Log::warning("DispatchNsfwScanJob: photo {$this->photo_id} not found, skipping.");

			return;
		}

		if (!$service->isConfigured()) {
			Log::warning("DispatchNsfwScanJob: NSFW service not configured, marking photo {$this->photo_id} as failed.");
			$photo->nsfw_status = NsfwStatus::FAILED;
			$photo->save();

			return;
		}

		$original = $photo->size_variants->getOriginal();

		if ($original === null) {
			Log::warning("DispatchNsfwScanJob: no original size variant for photo {$this->photo_id}, marking as failed.");
			$photo->nsfw_status = NsfwStatus::FAILED;
			$photo->save();

			return;
		}

		try {
			$photo->nsfw_status = NsfwStatus::PENDING;
			$photo->save();

			$response = $service->dispatchPhoto($this->photo_id, $original->short_path);

			if (!$response->successful()) {
				Log::warning("DispatchNsfwScanJob: NSFW service returned HTTP {$response->status()} for photo {$this->photo_id}.", ['response' => $response->json()]);
				$photo->nsfw_status = NsfwStatus::FAILED;
				$photo->save();
			}
		} catch (\Exception $e) {
			Log::warning("DispatchNsfwScanJob: request failed for photo {$this->photo_id}: " . $e->getMessage());
			$photo->nsfw_status = NsfwStatus::FAILED;
			$photo->save();
		}
	}

	public function failed(\Throwable $exception): void
	{
		Log::error("DispatchNsfwScanJob: final failure for photo {$this->photo_id}: " . $exception->getMessage());

		$photo = Photo::find($this->photo_id);
		if ($photo !== null) {
			$photo->nsfw_status = NsfwStatus::FAILED;
			$photo->save();
		}
	}
}
