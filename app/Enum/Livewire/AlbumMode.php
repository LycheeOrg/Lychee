<?php

namespace App\Enum\Livewire;

use Illuminate\Support\Str;

enum AlbumMode: int
{
	case SQUARE = 0;
	case JUSTIFIED = 1;
	case MASONRY = 2;
	case GRID = 3;

	/**
	 * get the name as a string instead of the value.
	 *
	 * @return string
	 */
	public function toCss(): string
	{
		return Str::lower($this->name);
	}
}
