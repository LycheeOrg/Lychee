<?php

namespace App\Console\Commands;

use App\Actions\Photo\Extensions\Constants;
use App\Contracts\ExternalLycheeException;
use App\Exceptions\Internal\NotImplementedException;
use App\Exceptions\UnexpectedException;
use App\Metadata\Extractor;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class ExifLens extends Command
{
	use Constants;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:exif_lens {from=0 : from which do we start} {nb=5 : generate exif data if missing} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get EXIF data from pictures if missing';

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
		try {
			$argument = $this->argument('nb');
			$from = $this->argument('from');
			$timeout = $this->argument('tm');
			set_time_limit($timeout);

			// we use lens because this is the one which is most likely to be empty.
			$photos = Photo::query()
				->where('lens', '=', '')
				->whereNotIn('type', $this->getValidVideoTypes())
				->offset($from)
				->limit($argument)
				->get();
			if (count($photos) == 0) {
				$this->line('No pictures requires EXIF updates.');

				return -1;
			}

			$i = $from;
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$fullPath = $photo->full_path;
				if (file_exists($fullPath)) {
					$info = $this->metadataExtractor->extract($fullPath, $photo->type);
					$updated = false;
					if ($photo->filesize == '' && $info['filesize'] != '') {
						$photo->filesize = $info['filesize'];
						$updated = true;
					}
					if ($photo->iso == '' && $info['iso'] != '') {
						$photo->iso = $info['iso'];
						$updated = true;
					}
					if ($photo->aperture == '' && $info['aperture'] != '') {
						$photo->aperture = $info['aperture'];
						$updated = true;
					}
					if ($photo->make == '' && $info['make'] != '') {
						$photo->make = $info['make'];
						$updated = true;
					}
					if ($photo->getAttribute('model') == '' && $info['model'] != '') {
						$photo->setAttribute('model', $info['model']);
						$updated = true;
					}
					if ($photo->lens == '' && $info['lens'] != '') {
						$photo->lens = $info['lens'];
						$updated = true;
					}
					if ($photo->shutter == '' && $info['shutter'] != '') {
						$photo->shutter = $info['shutter'];
						$updated = true;
					}
					if ($photo->focal == '' && $info['focal'] != '') {
						$photo->focal = $info['focal'];
						$updated = true;
					}
					if ($updated) {
						if ($photo->save()) {
							$this->line($i . ': EXIF updated for ' . $photo->title);
						} else {
							$this->line($i . ': Failed to update EXIF for ' . $photo->title);
						}
					} else {
						$this->line($i . ': Could not get EXIF data/nothing to update for ' . $photo->title . '.');
					}
				} else {
					$this->line($i . ': File does not exist for ' . $photo->title . '.');
				}
				$i++;
			}

			return 0;
		} catch (InvalidCastException | JsonEncodingException | \InvalidArgumentException | NotImplementedException | SymfonyConsoleException $e) {
			throw new UnexpectedException($e);
		}
	}
}
