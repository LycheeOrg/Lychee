<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

class FileDeletionException extends LycheeDomainException
{
	public function __construct(string $storage, string $path)
	{
		parent::__construct(sprintf('Storage::delete (%s) failed: %s', $storage, $path));
	}
}