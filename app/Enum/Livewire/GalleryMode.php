<?php

namespace App\Enum\Livewire;

use App\Enum\Traits\WireableEnumTrait;
use Livewire\Wireable;

enum GalleryMode: string implements Wireable
{
	use WireableEnumTrait;

	case ALBUM = 'album';
	case ALBUMS = 'albums';
	case PHOTO = 'photo';
	case MAP = 'map';
}
