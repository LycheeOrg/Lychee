<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Image;

use App\Actions\Photo\Delete;
use App\DTO\Nsfw\NsfwDetectionItemData;
use App\Enum\NsfwBlockFindingAction;
use App\Enum\NsfwDetectionLabel;
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
		if ($trust_level === null) {
			Log::warning("NsfwActionService: photo {$photo->id} has no upload_trust_level, defaulting to CHECK.");
			$trust_level = UserUploadTrustLevel::CHECK;
		}

		$was_moderated = false;

		if ($should_block) {
			$result = $this->applyBlockAction($photo, $trust_level);
			// null = hard-deleted, true = moderated, false = approved
			if ($result === null) {
				return false;
			}
			$was_moderated = $result;
		}

		if ($should_review && !$was_moderated) {
			$was_moderated = $this->applyReviewAction($photo, $trust_level);
			// true = moderated, false = approved
		}

		if ($is_sensitive) {
			$was_moderated = $this->applySensitiveAction($photo, $trust_level, $was_moderated) || $was_moderated;
		}

		if (!$was_moderated && $photo->nsfw_status !== NsfwStatus::VISIBLE) {
			$photo->nsfw_status = NsfwStatus::VISIBLE;
		}

		if (!$was_moderated && $trust_level !== UserUploadTrustLevel::CHECK) {
			$photo->is_validated = true;
		}

		$photo->save();

		return true;
	}

	/**
	 * Apply block actions based on the trust-tier × finding-tier matrix.
	 *
	 * @return bool|null null if photo was hard-deleted, true if moderated, false if approved
	 */
	private function applyBlockAction(Photo $photo, UserUploadTrustLevel $trust_level): ?bool
	{
		$action = $this->getBlockAction($trust_level);

		if ($action === NsfwBlockFindingAction::BLOCK) {
			Log::info("NsfwActionService: photo {$photo->id} hard-deleted (block finding, trust={$trust_level->value}).");
			$delete = resolve(Delete::class);
			$delete->forceDeletePhoto($photo->id);

			return null;
		}

		if ($action === NsfwBlockFindingAction::MODERATE) {
			Log::info("NsfwActionService: photo {$photo->id} moderated (block finding, trust={$trust_level->value}).");
			$photo->nsfw_status = NsfwStatus::REVIEW;
			$photo->is_validated = false;

			return true;
		}

		Log::info("NsfwActionService: photo {$photo->id} approved (block finding, trust={$trust_level->value}).");
		$photo->nsfw_status = NsfwStatus::VISIBLE;

		return false;
	}

	/**
	 * Apply review actions based on the trust-tier × finding-tier matrix.
	 *
	 * @return bool true if moderated, false if approved
	 */
	private function applyReviewAction(Photo $photo, UserUploadTrustLevel $trust_level): bool
	{
		if ($trust_level === UserUploadTrustLevel::CHECK || $trust_level === UserUploadTrustLevel::MONITOR) {
			Log::info("NsfwActionService: photo {$photo->id} moderated (review finding, trust={$trust_level->value}).");
			$photo->nsfw_status = NsfwStatus::REVIEW;
			$photo->is_validated = false;

			return true;
		}

		Log::info("NsfwActionService: photo {$photo->id} approved (review finding, trust={$trust_level->value}).");
		if ($photo->nsfw_status !== NsfwStatus::REVIEW) {
			$photo->nsfw_status = NsfwStatus::VISIBLE;
		}

		return false;
	}

	/**
	 * Apply sensitive actions based on the trust-tier × finding-tier matrix.
	 *
	 * @param Photo                $photo
	 * @param UserUploadTrustLevel $trust_level
	 * @param bool                 $was_moderated
	 *
	 * @return bool
	 */
	private function applySensitiveAction(Photo $photo, UserUploadTrustLevel $trust_level, bool $was_moderated): bool
	{
		if ($trust_level === UserUploadTrustLevel::CHECK) {
			if (!$was_moderated) {
				$photo->nsfw_status = NsfwStatus::REVIEW;
				$photo->is_validated = false;
			}
			Log::info("NsfwActionService: photo {$photo->id} moderated (sensitive finding, trust=check). Album action deferred to approval.");

			return !$was_moderated;
		}

		$album_action = $this->config_manager->getValueAsEnum('ai_vision_nsfw_sensitive_album_action', NsfwSensitiveAlbumAction::class);

		if ($album_action === NsfwSensitiveAlbumAction::MARK_ALBUM) {
			ApplyNsfwAlbumSensitivityJob::dispatch($photo->id);
			Log::info("NsfwActionService: dispatched album sensitivity job for photo {$photo->id} (sensitive finding, trust={$trust_level->value}).");
		}

		return false;
	}

	/**
	 * Log detection results to the nsfw_detections table.
	 * Deduplicated by photo_id+label+bbox.
	 *
	 * @param string                  $photo_id
	 * @param NsfwDetectionItemData[] $block_detected
	 * @param NsfwDetectionItemData[] $review_detected
	 * @param NsfwDetectionItemData[] $sensitive_detected
	 */
	public function logDetections(string $photo_id, array $block_detected, array $review_detected, array $sensitive_detected): void
	{
		$merged = [];

		foreach ($block_detected as $detection) {
			$key = $detection->detectionKey();
			$merged[$key] = $merged[$key] ?? $this->initDetection($photo_id, $detection);
			$merged[$key]['is_block'] = true;
		}

		foreach ($review_detected as $detection) {
			$key = $detection->detectionKey();
			$merged[$key] = $merged[$key] ?? $this->initDetection($photo_id, $detection);
			$merged[$key]['is_review'] = true;
		}

		foreach ($sensitive_detected as $detection) {
			$key = $detection->detectionKey();
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

		return $this->config_manager->getValueAsEnum($config_key, NsfwBlockFindingAction::class);
	}

	/**
	 * @param string                $photo_id
	 * @param NsfwDetectionItemData $detection
	 *
	 * @return array{photo_id:string,label:NsfwDetectionLabel,confidence:float,bbox_x:int,bbox_y:int,bbox_width:int,bbox_height:int,area_pixels:int,area_ratio:float,is_block:bool,is_review:bool,is_sensitive:bool}
	 */
	private function initDetection(string $photo_id, NsfwDetectionItemData $detection): array
	{
		return [
			'photo_id' => $photo_id,
			'label' => $detection->label,
			'confidence' => $detection->confidence,
			'bbox_x' => $detection->bbox->x,
			'bbox_y' => $detection->bbox->y,
			'bbox_width' => $detection->bbox->width,
			'bbox_height' => $detection->bbox->height,
			'area_pixels' => $detection->area_pixels,
			'area_ratio' => $detection->area_ratio,
			'is_block' => false,
			'is_review' => false,
			'is_sensitive' => false,
		];
	}
}
