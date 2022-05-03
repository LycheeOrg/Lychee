<?php

namespace App\Rules;

class DescriptionRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(true, 1000);
	}
}
