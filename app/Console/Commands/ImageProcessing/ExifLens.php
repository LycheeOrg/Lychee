<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Contracts\Exceptions\ExternalLycheeException;
use App\Enum\SizeVariantType;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnexpectedException;
use App\Image\Files\BaseMediaFile;
use App\Metadata\Extractor;
use App\Models\Photo;
use Illuminate\Console\Command;
use Safe\Exceptions\InfoException;
use function Safe\filemtime;
use function Safe\set_time_limit;

class ExifLens extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:exif_lens {offset=0 : from which do we start} {limit=5 : number of photos to generate exif data for} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get EXIF data from pictures if missing';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		try {
			$limit = (int) $this->argument('limit');
			$offset = (int) $this->argument('offset');
			$timeout = (int) $this->argument('tm');

			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			// we use lens because this is the one which is most likely to be empty.
			$photos = Photo::query()->with(['size_variants' => function ($r) {
				$r->where('type', '=', SizeVariantType::ORIGINAL);
			}])
				->where('lens', '=', '')
				->whereNotIn('type', BaseMediaFile::SUPPORTED_VIDEO_MIME_TYPES)
				->offset($offset)
				->limit($limit)
				->get();
			if (count($photos) === 0) {
				$this->line('No pictures requires EXIF updates.');

				return -1;
			}

			$i = $offset;
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				try {
					$localFile = $photo->size_variants->getOriginal()->getFile()->toLocalFile();
					$info = Extractor::createFromFile($localFile, filemtime($localFile->getRealPath()));
					$updated = false;
					if ($photo->size_variants->getOriginal()->filesize === 0) {
						$photo->size_variants->getOriginal()->filesize = $localFile->getFilesize();
						$updated = true;
					}
					if (
						($photo->iso === null || $photo->iso === '') &&
						$info->iso !== null &&
						$info->iso !== ''
					) {
						$photo->iso = $info->iso;
						$updated = true;
					}
					if (
						($photo->aperture === null || $photo->aperture === '') &&
						$info->aperture !== null &&
						$info->aperture !== ''
					) {
						$photo->aperture = $info->aperture;
						$updated = true;
					}
					if (
						($photo->make === null || $photo->make === '') &&
						$info->make !== null &&
						$info->make !== ''
					) {
						$photo->make = $info->make;
						$updated = true;
					}
					if (
						($photo->model === null || $photo->model === '') &&
						$info->model !== null &&
						$info->model !== ''
					) {
						$photo->model = $info->model;
						$updated = true;
					}
					if (
						($photo->lens === null || $photo->lens === '') &&
						$info->lens !== null &&
						$info->lens !== ''
					) {
						$photo->lens = $info->lens;
						$updated = true;
					}
					if (
						($photo->shutter === null || $photo->shutter === '') &&
						$info->shutter !== null &&
						$info->shutter !== ''
					) {
						$photo->shutter = $info->shutter;
						$updated = true;
					}
					if (
						($photo->focal === null || $photo->focal === '') &&
						$info->focal !== null &&
						$info->focal !== ''
					) {
						$photo->focal = $info->focal;
						$updated = true;
					}
					if ($updated) {
						$photo->save();
						$photo->size_variants->getOriginal()->save();
						$this->line($i . ': EXIF updated for ' . $photo->title);
					} else {
						$this->line($i . ': Could not get EXIF data/nothing to update for ' . $photo->title . '.');
					}
				} catch (ModelDBException $e) {
					$this->line($i . ': Failed to update EXIF for ' . $photo->title);
					$this->line($i . ': ' . $e->getMessage());
				}
				$i++;
			}

			return 0;
		} catch (\Throwable $e) {
			throw new UnexpectedException($e);
		}
	}
}
