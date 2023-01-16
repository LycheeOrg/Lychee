<?php

namespace App\Enum\Livewire;

use App\Enum\Traits\WireableEnumTrait;
use Livewire\Wireable;

enum PageMode: string implements Wireable
{
	use WireableEnumTrait;

	case GALLERY = 'gallery';
	case MAP = 'map';
	case SETTINGS = 'settings';
	case LOGS = 'logs';
	case DIAGNOSTICS = 'diagnostics';
}
