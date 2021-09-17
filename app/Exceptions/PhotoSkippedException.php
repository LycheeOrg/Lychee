<?php

namespace App\Exceptions;

class PhotoSkippedException extends BaseException
{
	public function __construct()
	{
		parent::__construct(409, 'The photo has been skipped');
	}
}
