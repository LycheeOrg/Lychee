<?php

namespace App\Rules;

class CopyrightRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(true, 300);
	}
}
