<?php

namespace App\Exceptions\Internal;

class MissingTranslationException extends LycheeInvalidArgumentException
{
	public function __construct(string $string)
	{
		parent::__construct('Missing translation for "' . $string . '"');
	}
}
