<?php

namespace App\Rules;

class CopyrightRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(false, 300);
	}
}
