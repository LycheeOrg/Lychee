<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum WebhookPayloadFormat.
 *
 * Determines how the webhook payload is delivered in outgoing HTTP requests.
 */
enum WebhookPayloadFormat: string
{
	use DecorateBackedEnum;

	/** Payload is sent as a JSON request body with Content-Type: application/json. */
	case JSON = 'json';

	/** Payload is sent as URL query parameters appended to the webhook URL. */
	case QUERY_STRING = 'query_string';
}
