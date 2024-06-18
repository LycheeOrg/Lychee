<?php

declare(strict_types=1);

namespace App\Rules;

class TitleRule extends StringRule
{
	public function __construct()
	{
		parent::__construct(false, 100);
	}
}
