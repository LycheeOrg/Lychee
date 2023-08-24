<?php

namespace App\Enum\Livewire;

enum PhotoOverlayMode: string
{
	case NONE = 'none';
	case DESC = 'desc';
	case EXIF = 'exif';
	case DATE = 'date';
}
