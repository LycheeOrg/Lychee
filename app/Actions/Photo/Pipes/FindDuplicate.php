<?php

namespace App\Actions\Photo\Pipes;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Image\StreamStat;
use App\Models\Photo;

/**
 * Assert wether we support said file.
 */
class FindDuplicate implements PhotoCreatePipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$checksum = StreamStat::createFromLocalFile($state->sourceFile)->checksum;

		/** @var Photo|null $photo */
		$state->duplicate = Photo::query()
			->where('checksum', '=', $checksum)
			->orWhere('original_checksum', '=', $checksum)
			->orWhere('live_photo_checksum', '=', $checksum)
			->first();

		return $next($state);
	}
}

