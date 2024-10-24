<?php

namespace App\Contracts\Http\Requests;

interface HasQuotaKB
{
	/**
	 * @return ?int
	 */
	public function quota_kb(): ?int;
}
