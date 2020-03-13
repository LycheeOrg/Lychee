<?php

namespace App\Console\Commands;

use App\Configs;
use App\Metadata\Extractor;
use App\ModelFunctions\Geodecoder;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Illuminate\Console\Command;

class decode_GPS_locations extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:decode_GPS_locations';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Decodes the GPS location data and adds street, city, country, etc. to the tags';

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
		$photos = Photo::whereNotNull('latitude')->whereNotNull('longitude')->whereNull('location')
			->get()
		;

		if (count($photos) == 0) {
			$this->line('No photos or videos require processing.');

			return 0;
		}

		$cachedProvider = Geodecoder::getGeocoderProvider();
		$this->line('Using ' . Configs::get_value('location_decoding_caching_type') . ' for chaching.');

		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');

			$photo->location = Geodecoder::decodeLocation_core($photo->latitude, $photo->longitude, $cachedProvider);
			$photo->save();
		}
	}
}
