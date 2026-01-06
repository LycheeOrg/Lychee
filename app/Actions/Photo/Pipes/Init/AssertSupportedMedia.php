<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Services\Image\FileExtensionService;

/**
 * Assert whether we support said file.
 */
class AssertSupportedMedia implements InitPipe
{
	public function __construct(
		private FileExtensionService $file_extension_service,
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$this->file_extension_service->assertIsSupportedMediaOrAcceptedRaw(
			$state->source_file->getPath(),
			$state->source_file->getMimeType(),
			$state->source_file->getOriginalExtension()
		);

		return $next($state);
	}
}