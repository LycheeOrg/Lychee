<?php

namespace App\Rules;

class TagsRule extends StringRule
{
	public function __construct(bool $isNullable)
	{
		parent::__construct($isNullable, 1000);
	}
}
