<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\SecurePath;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;

class SecurePathRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// We make sure that at least secure_image_link is enabled or temporary_image_link is enabled.
		// This ensures that the path provided by the potential attacker is encrypted with the key of the server or
		// That it the url is signed.

		// These two options options should guarantee that the path only under control of the owner of the server (hopefully).

		return Configs::getValueAsBool('secure_image_link_enabled') ||
			Configs::getValueAsBool('temporary_image_link_enabled');
	}
}
