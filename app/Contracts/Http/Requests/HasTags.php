<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasTags
{
	/**
	 * @return string[]
	 */
	public function tags(): array;
}
