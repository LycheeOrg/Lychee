<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum WebhookMethod.
 *
 * The HTTP methods allowed for outgoing webhook requests.
 */
enum WebhookMethod: string
{
	use DecorateBackedEnum;

	case GET = 'GET';
	case POST = 'POST';
	case PUT = 'PUT';
	case PATCH = 'PATCH';
	case DELETE = 'DELETE';
}
