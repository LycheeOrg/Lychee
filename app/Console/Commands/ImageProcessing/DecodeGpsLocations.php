<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Contracts\Exceptions\ExternalLycheeException;
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
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		// Update location if field 'location' is null or empty
		$photos = Photo::query()
			->whereNotNull('latitude')
			->whereNotNull('longitude')->where(
				function ($query): void {
					$query->where('location', '=', '')->orWhereNull('location');
				}
			)
			->get();

		if (count($photos) === 0) {
			$this->line('No photos or videos require processing.');

			return 0;
		}

		$cache_provider = Geodecoder::getGeocoderProvider();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');

			$photo->location = Geodecoder::decodeLocation_core($photo->latitude, $photo->longitude, $cache_provider);
			$photo->save();
		}

		return 0;
	}
}
