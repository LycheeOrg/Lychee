<?php

namespace App\Contracts\Http\Requests;

interface HasTags
{
	/**
	 * @return string[]
	 */
	public function tags(): array;
}
