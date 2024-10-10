<?php

namespace App\Contracts\Http\Requests;

use App\Http\Resources\Editable\EditableConfigResource;
use Illuminate\Support\Collection;

interface HasConfigs
{
	/**
	 * @return Collection<int,EditableConfigResource>
	 */
	public function configs(): Collection;
}
