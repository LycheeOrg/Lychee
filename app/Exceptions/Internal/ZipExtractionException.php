<?php

namespace App\Exceptions\Internal;

class ZipExtractionException extends LycheeDomainException
{
	public function __construct(string $path, string $to)
	{
		parent::__construct(sprintf('Could not extract %s to %s', $path, $to));
	}
}
