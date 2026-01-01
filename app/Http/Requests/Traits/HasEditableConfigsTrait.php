<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Http\Resources\Editable\EditableConfigResource;
use Illuminate\Support\Collection;

trait HasEditableConfigsTrait
{
	/** @var Collection<int,EditableConfigResource> */
	protected Collection $editable_configs;

	/**
	 * @return Collection<int,EditableConfigResource>
	 */
	public function editable_configs(): Collection
	{
		return $this->editable_configs;
	}
}
