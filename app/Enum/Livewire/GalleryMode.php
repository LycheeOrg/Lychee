<?php

namespace App\Enum\Livewire;

enum GalleryMode: string
{
	case ALBUM = 'album';
	case ALBUMS = 'albums';
	case PHOTO = 'photo';
	case MAP = 'map';
}
