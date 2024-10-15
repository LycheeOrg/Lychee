<?php

namespace App\Http\Requests\Traits;

trait HasQuotaKBTrait
{
	/**
	 * @var ?int
	 */
	protected ?int $quota_kb;

	/**
	 * @return ?int
	 */
	public function quota_kb(): ?int
	{
		return $this->quota_kb;
	}
}
