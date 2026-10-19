<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Constants\PhotoAlbum as PA;
use App\Enum\SizeVariantType;
use App\Events\PhotoSaved;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\Image\Handlers\GoogleMotionPictureHandler;
use App\Models\Album;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPExif\MotionPhoto\MotionPhotoLocator;
use Safe\Exceptions\InfoException;
use function Safe\set_time_limit;

/**
 * Extracts the video of Google Motion Photos that were imported as plain
 * stills, before Feature 100 recognised the Motion Photo 1.0 format.
 *
 * The original size variant keeps the whole camera file, video included, so
 * the video is cut from it and stored next to it, as the upload pipeline
 * does (FR-100-04).
 */
class ExtractMotionPhotos extends Command
{
	private const MIME_TYPE_JPEG = 'image/jpeg';

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:extract_motion_photos' .
		' {limit=100 : number of photos to check}' .
		' {tm=600 : timeout time requirement}' .
		' {--after= : continue after this photo id (printed by the previous run)}' .
		' {--album= : only check the photos of this album}' .
		' {--dry-run : list the motion photos without extracting their video}' .
		' {--reencode : extract again the video of motion photos that already have one, transcoding HEVC clips to H.264}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Extract the video of Google Motion Photos imported as plain photos';

	/**
	 * Execute the console command.
	 */
	public function handle(ConfigManager $config_manager): int
	{
		$limit = (int) $this->argument('limit');
		$timeout = (int) $this->argument('tm');
		$after = $this->option('after');
		$album_id = $this->option('album');
		$dry_run = $this->option('dry-run') === true;
		$reencode = $this->option('reencode') === true;

		try {
			set_time_limit($timeout);
		} catch (InfoException) {
			// Silently do nothing, if `set_time_limit` is denied.
		}

		if (!$dry_run && !$config_manager->hasFFmpeg()) {
			$this->error('FFmpeg is required to extract motion photo videos (or use --dry-run).');

			return 1;
		}
		if (is_string($album_id) && Album::query()->find($album_id) === null) {
			$this->error('Album ' . $album_id . ' not found.');

			return 1;
		}

		$photos = Photo::query()
			->with(['size_variants' => fn ($r) => $r->where('type', '=', SizeVariantType::ORIGINAL)])
			->where('type', '=', self::MIME_TYPE_JPEG)
			// Re-encoding only concerns Google motion photos: an Apple pair has a content id.
			->when(
				$reencode,
				fn ($q) => $q->whereNotNull('live_photo_short_path')->whereNull('live_photo_content_id'),
				fn ($q) => $q->whereNull('live_photo_short_path'),
			)
			->when(is_string($album_id), fn ($q) => $q->whereIn('id', DB::table(PA::PHOTO_ALBUM)->select(PA::PHOTO_ID)->where(PA::ALBUM_ID, '=', $album_id)))
			// Cursor, not offset: extracted photos leave this set, the others stay in it.
			->when(is_string($after) && $after !== '', fn ($q) => $q->where('id', '>', $after))
			->orderBy('id')
			->limit($limit)
			->get();

		if ($photos->isEmpty()) {
			$this->line('No photos require motion photo extraction.');

			return 0;
		}

		$found = [];
		$failed = 0;
		foreach ($photos as $photo) {
			$is_motion_photo = $this->process($photo, $dry_run);
			$failed += $is_motion_photo === null ? 1 : 0;
			if ($is_motion_photo === true) {
				$found[] = $photo->id;
				$this->line('Motion photo: ' . $photo->id . ' (' . $photo->title . ')');
			}
		}

		PhotoSaved::dispatchIf(!$dry_run && $found !== [], $found);

		$outcome = match (true) {
			$dry_run => 'found (dry run)',
			$reencode => 're-extracted',
			default => 'extracted',
		};
		$this->line(sprintf('Checked %d photos: %d motion photos %s, %d failed.', $photos->count(), count($found), $outcome, $failed));
		if ($photos->count() === $limit) {
			$this->line('More photos may remain: continue with --after=' . $photos->last()->id);
			$this->retryHint($failed, $after);
		}

		return $failed === 0 ? 0 : 1;
	}

	/**
	 * @return bool|null true for a motion photo (its video extracted unless `$dry_run`), false for a still, null if the photo could not be processed
	 */
	private function process(Photo $photo, bool $dry_run): ?bool
	{
		$local_file = null;
		try {
			$original = $photo->size_variants->getOriginal()->getFile();
			if (!$original->exists()) {
				throw new MediaFileOperationException('original file is missing');
			}

			$local_file = $this->toLocalFile($original, $photo);
			$video_length = MotionPhotoLocator::locate($local_file->getRealPath());
			if ($video_length > 0 && !$dry_run) {
				$this->extract($photo, $original, $local_file, $video_length);
			}

			return $video_length > 0;
		} catch (\Throwable $e) {
			Log::warning(__METHOD__ . ':' . __LINE__ . ' Motion photo extraction failed for photo ' . $photo->id . ': ' . $e->getMessage());
			$this->warn('Failed: ' . $photo->id . ' (' . $photo->title . '): ' . $e->getMessage());

			return null;
		} finally {
			if ($local_file instanceof TemporaryLocalFile) {
				$local_file->delete();
			}
		}
	}

	/**
	 * Continuing with the printed cursor would skip the photos that failed in
	 * this batch: tell the operator to run the batch again first.
	 */
	private function retryHint(int $failed, mixed $after): void
	{
		if ($failed === 0) {
			return;
		}

		$same_batch = is_string($after) && $after !== '' ? 'with --after=' . $after : 'without --after';
		$this->line(sprintf('%d photos failed in this batch and would be skipped by that cursor: run it again %s to retry them first.', $failed, $same_batch));
	}

	/**
	 * Returns the original itself when it is hosted locally, a downloaded
	 * temporary copy otherwise (as {@see \App\Image\SizeVariantDefaultFactory}).
	 */
	private function toLocalFile(FlysystemFile $original, Photo $photo): NativeLocalFile
	{
		if ($original->isLocalFile()) {
			return $original->toLocalFile();
		}

		// @codeCoverageIgnoreStart
		$local_file = new TemporaryLocalFile($original->getOriginalExtension(), $photo->title);
		$local_file->write($original->read(), false, $photo->type);

		return $local_file;
		// @codeCoverageIgnoreEnd
	}

	private function extract(Photo $photo, FlysystemFile $original, NativeLocalFile $local_file, int $video_length): void
	{
		$video_file = new TemporaryLocalFile(GoogleMotionPictureHandler::FINAL_VIDEO_FILE_EXTENSION, $local_file->getBasename());
		try {
			$handler = new GoogleMotionPictureHandler();
			$handler->load($local_file, $video_length);
			$handler->saveVideoStream($video_file);

			// A re-encoded clip replaces the stored one; a new clip goes next to the original.
			$target_path = $photo->live_photo_short_path ?? GoogleMotionPictureHandler::videoPathNextTo($original->getRelativePath());
			// Staged and moved over the target, so a failed write never truncates a clip in use.
			$staged_file = new FlysystemFile($original->getDisk(), $target_path . '.part');
			$staged_file->write($video_file->read());
			$staged_file->move($target_path);
			$photo->live_photo_short_path = $target_path;
			$photo->save();
		} finally {
			$video_file->close();
			$video_file->delete();
		}
	}
}
