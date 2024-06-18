<?php

declare(strict_types=1);

namespace App\Exceptions\Internal;

class ZeroModuloException extends LycheeDomainException
{
	public function __construct()
	{
		parent::__construct('Modulo equals zero');
	}
}
