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
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

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
	 * @throws SymfonyConsoleException
	 */
	public function __construct()
	{
		parent::__construct();
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
		// Update location if field 'location' is null or empty
		$photos = Photo::query()
			->whereNotNull('latitude')
			->whereNotNull('longitude')->where(
				function ($query) {
					$query->where('location', '=', '')->orWhereNull('location');
				}
			)
			->get();

		if (count($photos) === 0) {
			$this->line('No photos or videos require processing.');

			return 0;
		}

		$cachedProvider = Geodecoder::getGeocoderProvider();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');

			$photo->location = Geodecoder::decodeLocation_core($photo->latitude, $photo->longitude, $cachedProvider);
			$photo->save();
		}

		return 0;
	}
}
