<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests\Constants;

use LycheeVerify\Contract\Status;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\Exceptions\SupporterOnlyOperationException;

class FreeVerifyier implements VerifyInterface
{
	public function get_status(): Status
	{
		return Status::FREE_EDITION;
	}

	public function check(Status $required_status = Status::SUPPORTER_EDITION): bool
	{
		return $required_status === Status::FREE_EDITION;
	}

	public function is_supporter(): bool
	{
		return false;
	}

	public function is_plus(): bool
	{
		return false;
	}

	public function authorize(Status $required_status = Status::SUPPORTER_EDITION): void
	{
		if (!$this->check($required_status)) {
			throw new SupporterOnlyOperationException($required_status);
		}
	}

	public function when(mixed $valIfTrue, mixed $valIfFalse, Status $required_status = Status::SUPPORTER_EDITION): mixed
	{
		$retValue = $this->check($required_status) ? $valIfTrue : $valIfFalse;

		return is_callable($retValue) ? $retValue() : $retValue;
	}

	public function validate(): bool
	{
		return true;
	}
}
