<?php

namespace App\Exceptions\Internal;

class InvalidRotationDirectionException extends \InvalidArgumentException
{
	public function __construct()
	{
		parent::__construct('Rotation direction must either equal -1 or 1');
	}
}