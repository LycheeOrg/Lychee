<?php

namespace App\Enum;

use App\Enum\Traits\WireableEnumTrait;
use Livewire\Wireable;

enum AlbumMode: int implements Wireable
{
	use WireableEnumTrait;

	case FLKR = 0;
	case MASONRY = 1;
	case SQUARE = 2;
}
