<?php

namespace App\Http\Requests\Contracts;

interface HasTags
{
	/**
	 * @return string[]
	 */
	public function tags(): array;
}
