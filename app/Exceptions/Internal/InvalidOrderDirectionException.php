<?php

namespace App\Exceptions\Internal;

class InvalidOrderDirectionException extends LycheeDomainException
{
	public function __construct()
	{
		parent::__construct('Ordering direction must either equal "asc" or "desc"');
	}
}