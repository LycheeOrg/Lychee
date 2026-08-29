<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\DTO\MetadataWritePayload;
use App\Enum\JobStatus;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Image\Files\NativeLocalFile;
use App\Image\StreamStat;
use App\Metadata\Writer;
use App\Models\JobHistory;
use App\Models\Photo;
use App\Models\PhotoRating;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Embeds a photo's title/description/tags/owner-rating into its Original
 * and (when present) RAW files.
 *
 * Gated behind the `embed_metadata_in_files_enabled` config (off by
 * default) and requires `exiftool` (Feature 059 spec). Dispatched from
 * `PhotoController::update()`/`tags()`/`rate()` whenever those gates hold.
 *
 * Under the default `QUEUE_CONNECTION=sync`, `dispatch()` runs `handle()`
 * inline within the triggering HTTP request — so `handle()`'s entire body
 * is wrapped in a single top-level try/catch and never re-throws, exactly
 * like {@see WatermarkerJob::handle()}. An escaped exception here would
 * otherwise break the photo-edit response.
 */
class EmbedMetadataJob implements ShouldQueue, ShouldBeUnique
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	// Deduplicate jobs for the same photo for 60 seconds, so rapid
	// consecutive edits (e.g. title then tags) collapse into one write.
	public $uniqueFor = 60;

	protected Photo $photo;

	public function __construct(Photo $photo)
	{
		$this->photo = $photo;
	}

	public function uniqueId(): string
	{
		return 'embed-metadata:' . $this->photo->id;
	}

	public function handle(): void
	{
		if ($this->quickExit()) {
			return;
		}

		$history = new JobHistory();
		$history->owner_id = $this->photo->owner_id;
		$history->job = Str::limit(sprintf('Embed metadata for photo: %s.', $this->photo->id), 200);
		$history->status = JobStatus::READY;
		$history->save();

		try {
			$this->run($history);
		} catch (\Throwable $e) {
			// NFR-059-07: never let an exception escape handle() — under the
			// default sync queue driver this would otherwise propagate into
			// the triggering PhotoController action.
			Log::channel('jobs')->error('Embed metadata job failed for photo: ' . $this->photo->id, [
				'photo_id' => $this->photo->id,
				'exception' => $e,
			]);
			$history->status = JobStatus::FAILURE;
			$history->save();
		}
	}

	private function quickExit(): bool
	{
		/** @var ConfigManager $config_manager */
		$config_manager = resolve(ConfigManager::class);

		if (!$config_manager->getValueAsBool('embed_metadata_in_files_enabled')) {
			return true;
		}

		if (!$config_manager->hasExiftool()) {
			Log::channel('jobs')->warning('Embed metadata job skipped: exiftool is not available.', [
				'photo_id' => $this->photo->id,
			]);

			return true;
		}

		return false;
	}

	private function run(JobHistory $history): void
	{
		/** @var ConfigManager $config_manager */
		$config_manager = resolve(ConfigManager::class);
		$history->status = JobStatus::STARTED;
		$history->save();

		// Always re-read the photo's current DB state at execution time,
		// never a value captured at dispatch time, so a de-duplicated run
		// still reflects the final, latest field values.
		$payload = $this->buildPayload();

		$this->photo->load(['size_variants', 'tags']);
		$exiftool_path = $config_manager->getValueAsString('exiftool_path');
		$update_checksum = $config_manager->getValueAsBool('embed_metadata_update_checksum_enabled');

		$raw = $this->photo->size_variants->getRaw();
		$raw_success = $this->writeSizeVariant($raw, $payload, $exiftool_path, $update_checksum);

		$original = $this->photo->size_variants->getOriginal();
		$original_success = $this->writeSizeVariant($original, $payload, $exiftool_path, $update_checksum);

		$history->status = ($raw_success && $original_success) ? JobStatus::SUCCESS : JobStatus::FAILURE;
		$history->save();
	}

	/**
	 * Write the size variant data.
	 *
	 * @param SizeVariant|null     $variant
	 * @param MetadataWritePayload $payload
	 * @param string               $exiftool_path
	 * @param bool                 $update_checksum
	 *
	 * @return bool
	 */
	private function writeSizeVariant(?SizeVariant $variant, MetadataWritePayload $payload, string $exiftool_path, bool $update_checksum): bool
	{
		// Quick exit
		if ($variant === null) {
			return true;
		}

		// Another quick exit: only local files can be written to, so skip any non-local size variants.
		if ($variant->storage_disk !== StorageDiskType::LOCAL) {
			Log::channel('jobs')->warning('Embed metadata job skipped a non-local size variant.', [
				'photo_id' => $this->photo->id,
				'size_variant_id' => $variant->id,
			]);

			return true;
		}

		try {
			$local_file = $variant->getFile()->toLocalFile();
			/** @var Writer $writer */
			$writer = resolve(Writer::class);
			$writer->embed($local_file, $payload, $exiftool_path);
			$this->refreshVariantStats($variant, $local_file, $update_checksum);

			return true;
		} catch (\Throwable $e) {
			Log::channel('jobs')->warning('Embed metadata job failed to write a size variant.', [
				'photo_id' => $this->photo->id,
				'size_variant_id' => $variant->id,
				'exception' => $e,
			]);

			return false;
		}
	}

	/**
	 * Payload from the Photo data.
	 *
	 * @return MetadataWritePayload
	 */
	private function buildPayload(): MetadataWritePayload
	{
		$this->photo->refresh();
		$owner_rating = PhotoRating::query()
			->where('photo_id', $this->photo->id)
			->where('user_id', $this->photo->owner_id)
			->value('rating');

		return new MetadataWritePayload(
			title: $this->photo->title,
			description: $this->photo->description,
			tags: $this->photo->tags->pluck('name')->all(),
			rating: $owner_rating !== null ? (int) $owner_rating : null,
		);
	}

	/**
	 * Refreshes filesize (unconditional) and, for the Original variant only
	 * and only when enabled, the photo's checksum columns.
	 */
	private function refreshVariantStats(SizeVariant $variant, NativeLocalFile $local_file, bool $update_checksum): void
	{
		$stat = StreamStat::createFromLocalFile($local_file);

		$variant->filesize = $stat->bytes;
		$variant->save();

		if ($variant->type === SizeVariantType::ORIGINAL && $update_checksum) {
			$this->photo->checksum = $stat->checksum;
			$this->photo->original_checksum = $stat->checksum;
			$this->photo->save();
		}
	}
}
