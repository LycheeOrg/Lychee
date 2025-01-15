<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Exceptions\UnexpectedException;
use App\Image\PlaceholderEncoder;
use App\Models\SizeVariant;
use Illuminate\Console\Command;
use Safe\Exceptions\InfoException;
use function Safe\set_time_limit;

class EncodePlaceholders extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:encode_placeholders {limit=5 : number of photos to encode placeholders for} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Encode placeholders if size variant exists and image has not been encoded';

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

			$placeholders = SizeVariant::query()
				->where('short_path', 'LIKE', '%placeholder/%')
				->limit($limit)
				->get();
			if (count($placeholders) === 0) {
				$this->line('No placeholders require encoding.');

				return 0;
			}

			$placeholderEncoder = new PlaceholderEncoder();
			foreach ($placeholders as $placeholder) {
				$placeholderEncoder->do($placeholder);
			}

			return 0;
		} catch (\Throwable $e) {
			throw new UnexpectedException($e);
		}
	}
}
