<?php

namespace App\Http\Requests\Contracts;

interface HasTags
{
	const TAGS_ATTRIBUTE = 'tags';

	/**
	 * @return string|null
	 */
	public function tags(): ?string;
}
