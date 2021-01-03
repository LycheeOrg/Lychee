<?php

namespace App\Console\Commands;

use App\Metadata\Geodecoder;
use App\Models\Photo;
use Illuminate\Console\Command;

class DecodeGpsLocations extends Command
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
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// Update location if field 'location' is null or empty
		$photos = Photo::whereNotNull('latitude')->whereNotNull('longitude')->where(
			function ($query) {
				$query->where('location', '=', '')->orWhereNull('location');
			})
			->get();

		if (count($photos) == 0) {
			$this->line('No photos or videos require processing.');

			return 0;
		}

		$cachedProvider = Geodecoder::getGeocoderProvider();
		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');

			$photo->location = Geodecoder::decodeLocation_core($photo->latitude, $photo->longitude, $cachedProvider);
			$photo->save();
		}
	}
}
