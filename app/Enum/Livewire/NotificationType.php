<?php

declare(strict_types=1);

namespace App\Enum\Livewire;

enum NotificationType: string
{
	case SUCCESS = 'success';
	case ERROR = 'error';
	case INFO = 'info';
	case WARNING = 'warning';
}
