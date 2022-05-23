<?php

namespace App\Http\Requests;

abstract class BaseRouteParameterApiRequest extends BaseApiRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function validationData(): array
	{
		return array_replace_recursive($this->route()->parameters(), $this->input(), $this->allFiles());
	}
}
