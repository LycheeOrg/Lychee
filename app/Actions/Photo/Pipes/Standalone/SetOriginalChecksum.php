<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Image\StreamStat;

class SetOriginalChecksum implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Unfortunately, we must read the entire file once to create the
		// true, original checksum.
		// It does **not** suffice to use the stream statistics, when the
		// image file is loaded, because we cannot guarantee that the image
		// loader reads the file entirely in one pass.
		//  a) The image loader may decide to seek in the file, skip
		//     certain parts (like EXIF information), re-read chunks of the
		//     file multiple times or out-of-order.
		//  b) The image loader may not load the entire file, if the image
		//     stream is shorter than the file and followed by additional
		//     non-image information (e.g. as it is the case for Google
		//     Live Photos)
		$state->photo->original_checksum = StreamStat::createFromLocalFile($state->sourceFile)->checksum;

		return $next($state);
	}
}
