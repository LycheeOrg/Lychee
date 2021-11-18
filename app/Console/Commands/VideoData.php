<?php

namespace App\Console\Commands;

use App\Actions\Photo\Extensions\Constants;
use App\Contracts\SizeVariantFactory;
use App\Metadata\Extractor;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Console\Command;

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
	 * @return void
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
	 */
	public function handle(): int
	{
		set_time_limit($this->argument('timeout'));

		$this->line(
			sprintf(
				'Will attempt to generate up to %s video thumbnails/metadata with a timeout of %d seconds...',
				$this->argument('count'),
				$this->argument('timeout')
			)
		);

		$photos = Photo::query()
			->whereIn('type', $this->getValidVideoTypes())
			->where('width', '=', 0)
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
			$originalSizeVariant = $photo->size_variants->getSizeVariant(SizeVariant::ORIGINAL);
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
					$photo->latitude = $info['latitude'];
				}
				if ($photo->longitude == null && $info['longitude'] !== null) {
					$photo->longitude = $info['longitude'];
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
	}
}
