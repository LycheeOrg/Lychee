<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Contracts\Exceptions\ExternalLycheeException;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\SizeVariantFactory;
use App\Enum\SizeVariantType;
use App\Exceptions\UnexpectedException;
use App\Image\Files\BaseMediaFile;
use App\Metadata\Extractor;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Safe\Exceptions\InfoException;
use function Safe\filemtime;
use function Safe\set_time_limit;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class VideoData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:video_data {count=100 : number of videos to process} {timeout=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate video thumbnails and metadata if missing';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		$timeout = intval($this->argument('timeout'));
		$count = intval($this->argument('count'));
		try {
			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			$this->line(
				sprintf(
					'Will attempt to generate up to %d video thumbnails/metadata with a timeout of %d seconds...',
					$count,
					$timeout
				)
			);

			$photos = Photo::query()
				->with(['size_variants'])
				->whereIn('type', BaseMediaFile::SUPPORTED_VIDEO_MIME_TYPES)
				->whereDoesntHave('size_variants', function (Builder $query) {
					$query->where('type', '=', SizeVariantType::THUMB);
				})
				->take($count)
				->get();

			if (count($photos) === 0) {
				$this->line('No videos require processing');

				return 0;
			}

			// Initialize factory for size variants
			$sizeVariantFactory = resolve(SizeVariantFactory::class);
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$this->line('Processing ' . $photo->title . '...');
				$originalSizeVariant = $photo->size_variants->getOriginal();
				$file = $originalSizeVariant->getFile()->toLocalFile();

				$info = Extractor::createFromFile($file, filemtime($file->getRealPath()));

				if ($originalSizeVariant->width === 0 && $info->width !== 0) {
					$originalSizeVariant->width = $info->width;
				}
				if ($originalSizeVariant->height === 0 && $info->height !== 0) {
					$originalSizeVariant->height = $info->height;
				}
				if ($photo->focal === null) {
					$photo->focal = $info->focal;
				}
				if ($photo->aperture === null) {
					$photo->aperture = $info->aperture;
				}
				if ($photo->latitude === null) {
					$photo->latitude = $info->latitude;
				}
				if ($photo->longitude === null) {
					$photo->longitude = $info->longitude;
				}
				if ($photo->isDirty()) {
					$this->line('Updated metadata');
				}

				// TODO: Fix this line before PR; init needs more parameters
				$sizeVariantFactory->init($photo);
				$sizeVariantFactory->createSizeVariants();

				$photo->save();
			}

			return 0;
		} catch (SymfonyConsoleException|LycheeException|\InvalidArgumentException $e) {
			if ($e instanceof ExternalLycheeException) {
				throw $e;
			} else {
				throw new UnexpectedException($e);
			}
		}
	}
}
