<?php

namespace App\Enum\Livewire;

use App\Enum\Traits\WireableEnumTrait;
use Illuminate\Support\Str;
use Livewire\Wireable;

enum AlbumMode: int implements Wireable
{
	use WireableEnumTrait;

	case FLKR = 0;
	case MASONRY = 1;
	case SQUARE = 2;

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
