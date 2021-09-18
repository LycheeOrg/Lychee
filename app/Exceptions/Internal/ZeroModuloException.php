<?php

namespace App\Exceptions\Internal;

class ZeroModuloException extends \InvalidArgumentException
{
	public function __construct()
	{
		parent::__construct('Modulo equals zero');
	}
}