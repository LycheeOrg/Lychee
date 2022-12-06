<?php

namespace App\Enum;

/**
 * Enum LogType.
 */
enum SeverityType: string
{
	case EMERGENCY = 'emergency'; // 0
	case ALERT = 'alert'; // 1
	case CRITICAL = 'critical'; // 2
	case ERROR = 'error'; // 3
	case WARNING = 'warning'; // 4
	case NOTICE = 'notice'; // 5
	case INFO = 'info'; // 6
	case DEBUG = 'debug'; // 7
}