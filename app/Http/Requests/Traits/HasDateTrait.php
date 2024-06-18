<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

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