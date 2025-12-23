<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\SecurePath;

use App\Http\Requests\AbstractEmptyRequest;

class SecurePathRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// We make sure that at least secure_image_link is enabled or temporary_image_link is enabled.
		// This ensures that the path provided by the potential attacker is encrypted with the key of the server or
		// that the url is signed.

		// This should guarantee that only paths that should be shared are accessible.

		return $this->configs()->getValueAsBool('secure_image_link_enabled') ||
			$this->configs()->getValueAsBool('temporary_image_link_enabled');
	}
}
