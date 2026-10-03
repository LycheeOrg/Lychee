<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Enum\SizeVariantType;
use App\Events\PhotoSaved;
use App\Metadata\Extractor;
use App\Models\Photo;
use App\Services\Image\FileExtensionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Safe\Exceptions\InfoException;
use function Safe\filemtime;
use function Safe\set_time_limit;

/**
 * Feature 081 (FR-081-05): flags 360° photos among the photos never checked
 * (`photos.is_360 IS NULL`), i.e. photos uploaded before 360° detection.
 */
class Detect360 extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:detect_360 {offset=0 : from which do we start} {limit=100 : number of photos to check} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Detect 360° photos among the photos never checked';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$limit = (int) $this->argument('limit');
		$offset = (int) $this->argument('offset');
		$timeout = (int) $this->argument('tm');

		try {
			set_time_limit($timeout);
		} catch (InfoException) {
			// Silently do nothing, if `set_time_limit` is denied.
		}

		$photos = Photo::query()
			->with(['size_variants' => fn ($r) => $r->where('type', '=', SizeVariantType::ORIGINAL)])
			->whereNull('is_360')
			->whereNotIn('type', FileExtensionService::SUPPORTED_VIDEO_MIME_TYPES)
			->orderBy('id')
			->offset($offset)
			->limit($limit)
			->get();

		if ($photos->isEmpty()) {
			$this->line('No photos require 360° detection.');

			return 0;
		}

		$found = [];
		$failed = 0;
		foreach ($photos as $photo) {
			$is_360 = $this->detect($photo);
			$failed += $is_360 === null ? 1 : 0;
			if ($is_360 === true) {
				$found[] = $photo->id;
				$this->line('360° photo: ' . $photo->id . ' (' . $photo->title . ')');
			}
		}

		PhotoSaved::dispatchIf($found !== [], $found);

		$this->line(sprintf('Checked %d photos: %d 360° photos, %d failed.', $photos->count(), count($found), $failed));

		return $failed === 0 ? 0 : 1;
	}

	/**
	 * Reads the original and stores the 360° data.
	 *
	 * @return bool|null whether the photo is a 360° photo, null when its original could not be read
	 */
	private function detect(Photo $photo): ?bool
	{
		try {
			$local_file = $photo->size_variants->getOriginal()->getFile()->toLocalFile();
			$info = Extractor::createFromFile($local_file, filemtime($local_file->getRealPath()));
		} catch (\Throwable $e) {
			Log::warning(__METHOD__ . ':' . __LINE__ . ' 360° detection failed for photo ' . $photo->id . ': ' . $e->getMessage());

			return null;
		}

		$photo->is_360 = $info->is_360;
		$photo->pano_full_width = $info->pano_full_width;
		$photo->pano_full_height = $info->pano_full_height;
		$photo->pano_crop_left = $info->pano_crop_left;
		$photo->pano_crop_top = $info->pano_crop_top;
		$photo->save();

		return $info->is_360;
	}
}
