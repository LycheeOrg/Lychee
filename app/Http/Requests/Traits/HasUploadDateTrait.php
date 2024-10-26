<?php

namespace App\Http\Requests\Traits;

use Illuminate\Support\Carbon;

trait HasUploadDateTrait
{
	protected ?Carbon $upload_date = null;

	/**
	 * @return Carbon|null
	 */
	public function uploadDate(): ?Carbon
	{
		return $this->upload_date;
	}
}