<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

class InvalidRotationDirectionException extends LycheeDomainException
{
	public function __construct()
	{
		parent::__construct('Rotation direction must either equal -1 or 1');
	}
}