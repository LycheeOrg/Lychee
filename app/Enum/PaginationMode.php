<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum PaginationMode.
 *
 * All the allowed pagination UI modes for albums and photos.
 */
enum PaginationMode: string
{
	case INFINITE_SCROLL = 'infinite_scroll';
	case LOAD_MORE_BUTTON = 'load_more_button';
	case PAGE_NAVIGATION = 'page_navigation';
}
