<?php

namespace App\Legacy\V1\Requests\Traits;

trait HasTitleTrait
{
	protected ?string $title = null;

	/**
	 * @return string|null
	 */
	public function title(): ?string
	{
		return $this->title;
	}
}
