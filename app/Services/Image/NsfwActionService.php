<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Image;

use App\Actions\Photo\Delete;
use App\Enum\NsfwBlockFindingAction;
use App\Enum\NsfwSensitiveAlbumAction;
use App\Enum\NsfwStatus;
use App\Enum\UserUploadTrustLevel;
use App\Jobs\ApplyNsfwAlbumSensitivityJob;
use App\Models\NsfwDetection;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Log;

class NsfwActionService
{
	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Apply actions based on the trust-tier × finding-tier matrix.
	 *
	 * @return bool true if the photo still exists after actions, false if it was hard-deleted
	 */
	public function applyActions(Photo $photo, bool $should_block, bool $should_review, bool $is_sensitive): bool
	{
		$trust_level = $photo->upload_trust_level;
		$was_moderated = false;
		$was_deleted = false;

		// Block findings
		if ($should_block) {
			$action = $this->getBlockAction($trust_level);

			if ($action === NsfwBlockFindingAction::BLOCK) {
				$delete = resolve(Delete::class);
				$delete->forceDeletePhoto($photo->id);
				Log::info("NsfwActionService: photo {$photo->id} hard-deleted (block finding, trust={$trust_level->value}).");
				$was_deleted = true;
			} elseif ($action === NsfwBlockFindingAction::MODERATE) {
				$photo->nsfw_status = NsfwStatus::REVIEW;
				$photo->is_validated = false;
				$was_moderated = true;
				Log::info("NsfwActionService: photo {$photo->id} moderated (block finding, trust={$trust_level->value}).");
			} else {
				$photo->nsfw_status = NsfwStatus::VISIBLE;
				Log::info("NsfwActionService: photo {$photo->id} approved (block finding, trust={$trust_level->value}).");
			}
		}

		if ($was_deleted) {
			return false;
		}

		// Review findings
		if ($should_review && !$was_moderated) {
			if ($trust_level === UserUploadTrustLevel::CHECK || $trust_level === UserUploadTrustLevel::MONITOR) {
				$photo->nsfw_status = NsfwStatus::REVIEW;
				$photo->is_validated = false;
				$was_moderated = true;
				Log::info("NsfwActionService: photo {$photo->id} moderated (review finding, trust={$trust_level->value}).");
			} else {
				if ($photo->nsfw_status !== NsfwStatus::REVIEW) {
					$photo->nsfw_status = NsfwStatus::VISIBLE;
				}
				Log::info("NsfwActionService: photo {$photo->id} approved (review finding, trust={$trust_level->value}).");
			}
		}

		// Sensitive findings
		if ($is_sensitive) {
			if ($trust_level === UserUploadTrustLevel::CHECK) {
				if (!$was_moderated) {
					$photo->nsfw_status = NsfwStatus::REVIEW;
					$photo->is_validated = false;
					$was_moderated = true;
				}
				Log::info("NsfwActionService: photo {$photo->id} moderated (sensitive finding, trust=check). Album action deferred to approval.");
			} else {
				$album_action = NsfwSensitiveAlbumAction::tryFrom(
					$this->config_manager->getValueAsString('ai_vision_nsfw_sensitive_album_action')
				) ?? NsfwSensitiveAlbumAction::MARK_ALBUM;

				if ($album_action === NsfwSensitiveAlbumAction::MARK_ALBUM) {
					ApplyNsfwAlbumSensitivityJob::dispatch($photo->id);
					Log::info("NsfwActionService: dispatched album sensitivity job for photo {$photo->id} (sensitive finding, trust={$trust_level->value}).");
				}
			}
		}

		// Set visible if no action was taken
		if (!$was_moderated && $photo->nsfw_status !== NsfwStatus::VISIBLE) {
			$photo->nsfw_status = NsfwStatus::VISIBLE;
		}

		// Hide-on-scan restore: if no block/review findings caused moderation
		// and the photo's trust level is not CHECK, unconditionally set is_validated = true.
		if (!$was_moderated && $trust_level !== UserUploadTrustLevel::CHECK) {
			$photo->is_validated = true;
		}

		$photo->save();

		return true;
	}

	/**
	 * Log detection results to the nsfw_detections table.
	 * Deduplicated by photo_id+label+bbox.
	 */
	public function logDetections(string $photo_id, array $block_detected, array $review_detected, array $sensitive_detected): void
	{
		$merged = [];

		foreach ($block_detected as $detection) {
			$key = $this->detectionKey($detection);
			$merged[$key] = $merged[$key] ?? $this->initDetection($photo_id, $detection);
			$merged[$key]['is_block'] = true;
		}

		foreach ($review_detected as $detection) {
			$key = $this->detectionKey($detection);
			$merged[$key] = $merged[$key] ?? $this->initDetection($photo_id, $detection);
			$merged[$key]['is_review'] = true;
		}

		foreach ($sensitive_detected as $detection) {
			$key = $this->detectionKey($detection);
			$merged[$key] = $merged[$key] ?? $this->initDetection($photo_id, $detection);
			$merged[$key]['is_sensitive'] = true;
		}

		foreach ($merged as $data) {
			NsfwDetection::create($data);
		}
	}

	private function getBlockAction(?UserUploadTrustLevel $trust_level): NsfwBlockFindingAction
	{
		$config_key = match ($trust_level) {
			UserUploadTrustLevel::CHECK => 'ai_vision_nsfw_check_block_action',
			UserUploadTrustLevel::MONITOR => 'ai_vision_nsfw_monitor_block_action',
			UserUploadTrustLevel::TRUST_BUT_VERIFY => 'ai_vision_nsfw_trust_but_verify_block_action',
			UserUploadTrustLevel::TRUSTED => 'ai_vision_nsfw_trust_block_action',
			default => 'ai_vision_nsfw_check_block_action',
		};

		return NsfwBlockFindingAction::tryFrom(
			$this->config_manager->getValueAsString($config_key)
		) ?? NsfwBlockFindingAction::BLOCK;
	}

	private function detectionKey(array $detection): string
	{
		$bbox = $detection['bbox'] ?? [];

		return implode(':', [
			$detection['label'] ?? '',
			$bbox['x'] ?? 0,
			$bbox['y'] ?? 0,
			$bbox['width'] ?? 0,
			$bbox['height'] ?? 0,
		]);
	}

	private function initDetection(string $photo_id, array $detection): array
	{
		$bbox = $detection['bbox'] ?? [];

		return [
			'photo_id' => $photo_id,
			'label' => $detection['label'] ?? '',
			'confidence' => $detection['confidence'] ?? 0.0,
			'bbox_x' => $bbox['x'] ?? 0,
			'bbox_y' => $bbox['y'] ?? 0,
			'bbox_width' => $bbox['width'] ?? 0,
			'bbox_height' => $bbox['height'] ?? 0,
			'area_pixels' => $detection['area_pixels'] ?? null,
			'area_ratio' => $detection['area_ratio'] ?? null,
			'is_block' => false,
			'is_review' => false,
			'is_sensitive' => false,
		];
	}
}
