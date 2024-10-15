<?php

namespace App\Http\Requests\Traits;

trait HasNoteTrait
{
	protected ?string $note = null;

	/**
	 * @return string|null
	 */
	public function note(): ?string
	{
		return $this->note;
	}
}
