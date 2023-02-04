<?php

namespace App\Enum\Livewire;

use App\Enum\Traits\WireableEnumTrait;
use Livewire\Wireable;

enum PageMode: string implements Wireable
{
	use WireableEnumTrait;

	case GALLERY = 'gallery';
	case MAP = 'map';
	case PROFILE = 'profile';
	case USERS = 'users';
	case SETTINGS = 'settings';
	case ALL_SETTINGS = 'all-settings';
	case LOGS = 'logs';
	case DIAGNOSTICS = 'diagnostics';
	case LANDING = 'landing';
}
