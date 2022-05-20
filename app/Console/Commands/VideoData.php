<?php

namespace App\Console\Commands;

use App\Actions\Photo\Extensions\Constants;
use App\Contracts\ExternalLycheeException;
use App\Contracts\LycheeException;
use App\Contracts\SizeVariantFactory;
use App\Exceptions\UnexpectedException;
use App\Metadata\Extractor;
use App\Models\Photo;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class VideoData extends Command
{
	use Constants;

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
	 * @var Extractor
	 */
	private Extractor $metadataExtractor;

	/**
	 * Create a new command instance.
	 *
	 * @param Extractor $metadataExtractor
	 *
	 * @throws SymfonyConsoleException
	 */
	public function __construct(Extractor $metadataExtractor)
	{
		parent::__construct();
		$this->metadataExtractor = $metadataExtractor;
	}

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
			\Safe\set_time_limit($timeout);

			$this->line(
				\Safe\sprintf(
					'Will attempt to generate up to %s video thumbnails/metadata with a timeout of %d seconds...',
					$count,
					$timeout
				)
			);

			$photos = Photo::query()
				->with(['size_variants'])
				->whereIn('type', $this->getValidVideoTypes())
				->where('width', '=', 0)
				->take($count)
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
				$fullPath = $originalSizeVariant->full_path;

				if (file_exists($fullPath)) {
					$info = $this->metadataExtractor->extract($fullPath, 'video');

					if ($originalSizeVariant->width == 0 && $info['width'] !== 0) {
						$originalSizeVariant->width = $info['width'];
					}
					if ($originalSizeVariant->height == 0 && $info['height'] !== 0) {
						$originalSizeVariant->height = $info['height'];
					}
					if ($photo->focal == '' && $info['focal'] !== '') {
						$photo->focal = $info['focal'];
					}
					if ($photo->aperture == '' && $info['aperture'] !== '') {
						$photo->aperture = $info['aperture'];
					}
					if ($photo->latitude == null && $info['latitude'] !== null) {
						$photo->latitude = floatval($info['latitude']);
					}
					if ($photo->longitude == null && $info['longitude'] !== null) {
						$photo->longitude = floatval($info['longitude']);
					}
					if ($photo->isDirty()) {
						$this->line('Updated metadata');
					}

					$sizeVariantFactory->init($photo);
					$sizeVariantFactory->createSizeVariants();
				} else {
					$this->line('File does not exist');
				}

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
