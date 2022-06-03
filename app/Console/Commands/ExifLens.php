<?php

namespace App\Console\Commands;

use App\Contracts\ExternalLycheeException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnexpectedException;
use App\Image\MediaFile;
use App\Metadata\Extractor;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExifLens extends Command
{
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
			$photos = Photo::with(['size_variants' => function (HasMany $r) {
				$r->where('type', '=', SizeVariant::ORIGINAL);
			}])
				->where('lens', '=', '')
				->whereNotIn('type', MediaFile::SUPPORTED_VIDEO_MIME_TYPES)
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
				try {
					$localFile = $photo->size_variants->getOriginal()->getFile()->toLocalFile();
					$info = Extractor::createFromFile($localFile);
					$updated = false;
					if ($photo->size_variants->getOriginal()->filesize === 0) {
						$photo->size_variants->getOriginal()->filesize = $localFile->getFilesize();
						$updated = true;
					}
					if (empty($photo->iso) && !empty($info->iso)) {
						$photo->iso = $info->iso;
						$updated = true;
					}
					if (empty($photo->aperture) && !empty($info->aperture)) {
						$photo->aperture = $info->aperture;
						$updated = true;
					}
					if (empty($photo->make) && !empty($info->make)) {
						$photo->make = $info->make;
						$updated = true;
					}
					if (empty($photo->model) && !empty($info->model)) {
						$photo->model = $info->model;
						$updated = true;
					}
					if (empty($photo->lens) && !empty($info->lens)) {
						$photo->lens = $info->lens;
						$updated = true;
					}
					if (empty($photo->shutter) && !empty($info->shutter)) {
						$photo->shutter = $info->shutter;
						$updated = true;
					}
					if (empty($photo->focal) && !empty($info->focal)) {
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
