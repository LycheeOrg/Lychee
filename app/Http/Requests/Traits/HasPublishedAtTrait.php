<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use Illuminate\Support\Carbon;

trait HasPublishedAtTrait
{
	private ?Carbon $published_at = null;
	private bool $published_at_provided = false;

	public function publishedAt(): ?Carbon
	{
		return $this->published_at;
	}

	public function publishedAtProvided(): bool
	{
		return $this->published_at_provided;
	}
}
