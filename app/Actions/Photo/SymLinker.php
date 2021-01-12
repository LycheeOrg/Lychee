<?php

namespace App\Actions\Photo;

use App\ModelFunctions\SymLinkFunctions;

class SymLinker
{
	/** @var SymLinkFunctions */
	protected $symLinkFunctions;

	public function __construct(SymLinkFunctions $symLinkFunctions)
	{
		$this->symLinkFunctions = $symLinkFunctions;
	}
}
