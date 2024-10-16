<?php

namespace App\Enum;

/**
 * Enum ConfigType.
 *
 * The most important type possibilities.
 */
enum ConfigType: string
{
	case INT = 'int';
	case POSTIIVE = 'positive';
	case STRING = 'string';
	case STRING_REQ = 'string_required';
	case BOOL = '0|1';
	case TERNARY = '0|1|2';
	case DISABLED = '';
	case LICENSE = 'license';
	case MAP_PROVIDER = 'map_provider';
}