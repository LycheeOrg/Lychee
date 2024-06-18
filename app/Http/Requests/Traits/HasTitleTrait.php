<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

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
