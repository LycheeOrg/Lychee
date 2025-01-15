<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Http\Resources\Editable\EditableConfigResource;
use Illuminate\Support\Collection;

trait HasConfigsTrait
{
	/** @var Collection<int,EditableConfigResource> */
	protected Collection $configs;

	/**
	 * @return Collection<int,EditableConfigResource>
	 */
	public function configs(): Collection
	{
		return $this->configs;
	}
}
