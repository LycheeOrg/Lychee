<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Exceptions\UnexpectedException;
use App\Jobs\ExtractColoursJob;
use App\Models\Photo;
use Illuminate\Console\Command;
use Safe\Exceptions\InfoException;
use function Safe\set_time_limit;

class ExtractColourPalette extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:extract_colour_palette {limit=5 : number of photos to extract the colour palette for} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Extract the colour palette if it has not been extracted yet';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		try {
			$limit = (int) $this->argument('limit');
			$timeout = (int) $this->argument('tm');

			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			$photos = Photo::with(['size_variants'])
				->whereDoesntHave('palette')
				->where('type', 'like', 'image/%')
				->orderBy('id')
				->lazyById($limit);

			if (count($photos) === 0) {
				$this->line('No photos require palette extraction.');

				return 0;
			}

			foreach ($photos as $photo) {
				$this->line(sprintf('Extracting Color Palette for %s [%s].', $photo->title, $photo->id));
				ExtractColoursJob::dispatchSync($photo);
			}

			return 0;
		} catch (\Throwable $e) {
			throw new UnexpectedException($e);
		}
	}
}
