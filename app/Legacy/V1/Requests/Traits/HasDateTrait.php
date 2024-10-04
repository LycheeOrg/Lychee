<?php

namespace App\Legacy\V1\Requests\Traits;

use Illuminate\Support\Carbon;

trait HasDateTrait
{
	protected ?Carbon $date = null;

	/**
	 * @return Carbon|null
	 */
	public function requestDate(): ?Carbon
	{
		return $this->date;
	}
}