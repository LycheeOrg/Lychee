<?php

namespace App\Console\Commands;

use App\Contracts\ExternalLycheeException;
use App\Contracts\LycheeException;
use App\Contracts\SizeVariantFactory;
use App\Exceptions\UnexpectedException;
use App\Image\MediaFile;
use App\Metadata\Extractor;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
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
		try {
			set_time_limit($this->argument('timeout'));

			$this->line(
				sprintf(
					'Will attempt to generate up to %s video thumbnails/metadata with a timeout of %d seconds...',
					$this->argument('count'),
					$this->argument('timeout')
				)
			);

			$photos = Photo::query()
				->with(['size_variants'])
				->whereIn('type', MediaFile::SUPPORTED_VIDEO_MIME_TYPES)
				->whereDoesntHave('size_variants', function (Builder $query) {
					$query->where('type', '=', SizeVariant::THUMB);
				})
				->take($this->argument('count'))
				->get();

			if (count($photos) == 0) {
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

				$info = Extractor::createFromFile($file);

				if ($originalSizeVariant->width == 0 && $info->width !== 0) {
					$originalSizeVariant->width = $info->width;
				}
				if ($originalSizeVariant->height == 0 && $info->height !== 0) {
					$originalSizeVariant->height = $info->height;
				}
				if (empty($photo->focal) && !empty($info->focal)) {
					$photo->focal = $info->focal;
				}
				if (empty($photo->aperture) && !empty($info->aperture)) {
					$photo->aperture = $info->aperture;
				}
				if ($photo->latitude == null && $info->latitude !== null) {
					$photo->latitude = $info->latitude;
				}
				if ($photo->longitude == null && $info->longitude) {
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
