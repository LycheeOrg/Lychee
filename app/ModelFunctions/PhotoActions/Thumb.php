<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions\PhotoActions;

use App\Assets\Helpers;
use App\Photo;
use Illuminate\Support\Facades\Storage;

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

	public function from_photo(Photo $photo): void
	{
		$this->types = $photo->type;
		$this->thumbIDs = $photo->id;
		$this->thumbs = Storage::url('thumb/' . $photo->thumb);
		if ($photo->thumb2x == '1') {
			$this->set_thumb2x();
		}
	}

	public function insertToArrays(array &$thumb, array &$type, array &$thumb2x): void
	{
		$thumb[] = $this->thumb;
		$type[] = $this->type;
		$thumb2x[] = $this->thumb2x;
	}
}
