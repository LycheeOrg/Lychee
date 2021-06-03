<?php

namespace App\Console\Commands;

use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\ImageEditing;
use App\Actions\Photo\Extensions\VideoEditing;
use App\Metadata\Extractor;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class VideoData extends Command
{
	use Constants;
	use VideoEditing;
	use ImageEditing;

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
	 * @return mixed
	 */
	public function handle()
	{
		set_time_limit($this->argument('timeout'));

		$this->line(
			sprintf(
				'Will attempt to generate up to %s video thumbnails/metadata with a timeout of %d seconds...',
				$this->argument('count'),
				$this->argument('timeout')
			)
		);

		$photos = Photo::whereIn('type', $this->getValidVideoTypes())
			->where('width', '=', 0)
			->take($this->argument('count'))
			->get();

		if (count($photos) == 0) {
			$this->line('No videos require processing');

			return 0;
		}

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');
			$path = Storage::path($photo->url);

			if ($photo->thumb_url != '') {
				$thumb = Storage::path($photo->thumb_url);
				if (file_exists($thumb)) {
					$basename = explode('.', $photo->filename);
					$thumbBasename = explode('.', $photo->thumb_filename);
					if ($basename[0] !== $thumbBasename[0]) {
						$photo->thumb_filename = $basename[0] . '.' . $thumbBasename[1];
						rename($thumb, Storage::path('thumb/') . $photo->thumb_filename);
						$this->line('Renamed thumb to match the video file');
					}
				}
			}

			if (file_exists($path)) {
				$info = $this->metadataExtractor->extract($path, $photo->type);

				$updated = false;
				if ($photo->width == 0 && $info['width'] !== 0) {
					$photo->width = $info['width'];
					$updated = true;
				}
				if ($photo->height == 0 && $info['height'] !== 0) {
					$photo->height = $info['height'];
					$updated = true;
				}
				if ($photo->focal == '' && $info['focal'] !== '') {
					$photo->focal = $info['focal'];
					$updated = true;
				}
				if ($photo->aperture == '' && $info['aperture'] !== '') {
					$photo->aperture = $info['aperture'];
					$updated = true;
				}
				if ($photo->latitude == null && $info['latitude'] !== null) {
					$photo->latitude = $info['latitude'];
					$updated = true;
				}
				if ($photo->longitude == null && $info['longitude'] !== null) {
					$photo->longitude = $info['longitude'];
					$updated = true;
				}
				if ($updated) {
					$this->line('Updated metadata');
				}

				if ($photo->thumb_filename === '' || $photo->thumb2x === false || $photo->small_width === null || $photo->small2x_width === null) {
					$frame_tmp = '';
					try {
						$frame_tmp = $this->extractVideoFrame($photo);
					} catch (\Exception $exception) {
						$this->line($exception->getMessage());
					}
					if ($frame_tmp !== '') {
						$this->line('Extracted video frame for thumbnails');
						if ($photo->thumb_filename === '' || $photo->thumb2x === false) {
							if (!$this->createThumb($photo, $frame_tmp)) {
								$this->line('Could not create thumbnail for video');
							}
							$basename = explode('.', $photo->filename);
							$photo->thumb_filename = $basename[0] . '.jpeg';
						}
						if ($photo->small_width === null || $photo->small2x_width === null) {
							$this->createSmallerImages($photo, $frame_tmp);
						}
						unlink($frame_tmp);
					}
				}
			} else {
				$this->line('File does not exist');
			}

			$photo->save();
		}
	}
}
