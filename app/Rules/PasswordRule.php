<?php

declare(strict_types=1);

namespace App\Rules;

class PasswordRule extends StringRule
{
	public function __construct(bool $isNullable)
	{
		parent::__construct($isNullable, 100);
	}
}
