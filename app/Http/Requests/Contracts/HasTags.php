<?php

namespace App\Http\Requests\Contracts;

interface HasTags
{
	public const TAGS_ATTRIBUTE = 'tags';

	/**
	 * TODO: Tags should be transmitted as a proper JSON array.
	 *
	 * @return string|null
	 */
	public function tags(): ?string;
}
