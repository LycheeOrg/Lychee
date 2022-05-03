<?php

namespace App\Rules;

class UsernameRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(false, 100);
	}
}
