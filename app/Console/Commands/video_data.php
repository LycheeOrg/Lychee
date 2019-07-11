<?php

namespace App\Console\Commands;

use App\Logs;
use App\Metadata\Extractor;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Illuminate\Console\Command;
use Storage;

class video_data extends Command
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
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * @var Extractor
	 */
	private $metadataExtractor;

	/**
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions $photoFunctions
	 *
	 * @return void
	 */
	public function __construct(PhotoFunctions $photoFunctions, Extractor $metadataExtractor)
	{
		parent::__construct();

		$this->photoFunctions = $photoFunctions;
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

		$photos = Photo::where('type', 'like', 'video/%')
			->where('width', '=', 0)
			->take($this->argument('count'))
			->get()
		;

		if (count($photos) == 0) {
			$this->line('No videos require processing');

			return 0;
		}

		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');
			$url = Storage::path('big/' . $photo->url);

			if ($photo->thumbUrl != '') {
				$thumb = Storage::path('thumb/') . $photo->thumbUrl;
				if (file_exists($thumb)) {
					$urlBase = explode('.', $photo->url);
					$thumbBase = explode('.', $photo->thumbUrl);
					if ($urlBase !== $thumbBase) {
						$photo->thumbUrl = $urlBase[0] . '.' . $thumbBase[1];
						rename($thumb, Storage::path('thumb/') . $photo->thumbUrl);
						$this->line('Renamed thumb to match the video file');
					}
				}
			}

			if (file_exists($url)) {
				$info = $this->metadataExtractor->extract($url, $photo->type);

				if ($info['width'] !== 0) {
					$this->line('Extracted metadata');
					$photo->width = $info['width'];
				}
				if ($info['height'] !== 0) {
					$photo->height = $info['height'];
				}
				if ($info['focal'] !== '') {
					$photo->focal = $info['focal'];
				}
				if ($info['aperture'] !== '') {
					$photo->aperture = $info['aperture'];
				}
				if ($info['takestamp'] !== null) {
					$photo->takestamp = $info['takestamp'];
				}
				if ($info['latitude'] !== null) {
					$photo->latitude = $info['latitude'];
				}
				if ($info['longitude'] !== null) {
					$photo->longitude = $info['longitude'];
				}

				if ($photo->thumbUrl === '' || $photo->thumb2x === 0 || $photo->small === '' || $photo->small2x === '') {
					$frame_tmp = '';
					try {
						$frame_tmp = $this->photoFunctions->extractVideoFrame($photo);
					} catch (\Exception $exception) {
						Logs::error(__METHOD__, __LINE__, $exception->getMessage());
					}
					if ($frame_tmp !== '') {
						$this->line('Extracted video frame for thumbnails');
						if ($photo->thumbUrl === '' || $photo->thumb2x === 0) {
							if (!$this->photoFunctions->createThumb($photo, $frame_tmp)) {
								Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for video');
							}
						}
						if ($photo->small === '' || $photo->small2x === '') {
							$this->photoFunctions->createSmallerImages($photo, $frame_tmp);
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
