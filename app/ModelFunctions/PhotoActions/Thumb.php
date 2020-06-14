<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions\PhotoActions;

use App\Assets\Helpers;

class Thumb
{
	public $thumb = '';
	public $type = '';
	public $thumb2x = '';
	public $thumbID = null;

	public function __construct(string $type, int $thumbID)
	{
		$this->type = $type;
		$this->thumbID = $thumbID;
	}

	public function set_thumb2x(): void
	{
		$this->thumb2x = Helpers::ex2x($this->thumb);
	}

	public function insertToArrays(array &$thumb, array &$type, array &$thumb2x): void
	{
		$thumb[] = $this->thumb;
		$type[] = $this->type;
		$thumb2x[] = $this->thumb2x;
	}
}
