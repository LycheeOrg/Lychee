<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;

/**
 * Assert whether we support said file.
 */
class AssertSupportedMedia implements InitPipe
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$state->sourceFile->assertIsSupportedMediaOrAcceptedRaw();

		return $next($state);
	}
}