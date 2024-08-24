<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Exceptions\MediaFileOperationException;
use App\Image\PlaceholderEncoder;

class EncodePlaceholder implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		try {
			$placeholderEncoder = new PlaceholderEncoder();
			$placeholder = $state->getPhoto()->size_variants->getPlaceholder();
			$originalFile = $placeholder->getFile();
			$placeholderEncoder->do($placeholder);
			// delete original file since we now have no reference to it
			$originalFile->delete();

			return $next($state);
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to encode placeholder to base64', $e);
		}
	}
}