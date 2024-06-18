<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Image\StreamStat;
use App\Models\Photo;

/**
 * Look for duplicates of the file in the database.
 */
class FindDuplicate implements InitPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$checksum = StreamStat::createFromLocalFile($state->sourceFile)->checksum;

		$state->duplicate = Photo::query()
			->where('checksum', '=', $checksum)
			->orWhere('original_checksum', '=', $checksum)
			->orWhere('live_photo_checksum', '=', $checksum)
			->first();

		return $next($state);
	}
}

