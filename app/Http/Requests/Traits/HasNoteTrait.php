<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasNoteTrait
{
	protected ?string $note = null;

	/**
	 * @return string|null
	 */
	public function note(): ?string
	{
		return $this->note;
	}
}
