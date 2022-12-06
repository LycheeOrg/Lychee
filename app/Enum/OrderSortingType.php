<?php

namespace App\Enum;

/**
 * Enum OrderSortingType.
 */
enum OrderSortingType: string
{
	case ASC = 'ASC';
	case DESC = 'DESC';

	// Just to be safe
	case asc = 'asc';
	case desc = 'desc';
}

