<?php

namespace App\Http\Requests\Contracts;

interface HasTags
{
	public const TAGS_ATTRIBUTE = 'tags';

	/**
	 * @return string[]
	 */
	public function tags(): array;
}
