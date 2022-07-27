<?php

namespace App\Rules;

class TitleRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(false, 100);
	}
}
