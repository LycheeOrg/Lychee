<?php

namespace App\Http\Requests\Traits;

trait HasLicenseTrait
{
	protected string $license = 'none';

	/**
	 * @return string
	 */
	public function license(): string
	{
		return $this->license;
	}
}