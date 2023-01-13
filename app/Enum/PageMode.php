<?php

namespace App\Enum;

use App\Enum\Traits\WireableEnumTrait;
use Livewire\Wireable;

enum PageMode: string implements Wireable
{
	use WireableEnumTrait;

	case ALBUM = 'album';
	case ALBUMS = 'albums';
	case PHOTO = 'photo';
	case MAP = 'map';
	case SETTINGS = 'settings';
}
