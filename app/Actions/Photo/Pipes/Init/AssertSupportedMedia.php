<?php

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;

/**
 * Assert wether we support said file.
 */
class AssertSupportedMedia implements PhotoCreatePipe
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->sourceFile->assertIsSupportedMediaOrAcceptedRaw();

		return $next($state);
	}
}