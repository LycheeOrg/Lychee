<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasTags
{
	/**
	 * @return string[]
	 */
	public function tags(): array;
}
