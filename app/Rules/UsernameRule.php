<?php

declare(strict_types=1);

namespace App\Rules;

class UsernameRule extends StringRule
{
	public function __construct(bool $nullable = false)
	{
		parent::__construct($nullable, 100);
	}
}
