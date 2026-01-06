<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Constants;

use LycheeVerify\Contract\Status;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\Exceptions\SupporterOnlyOperationException;

class ProVerifyier implements VerifyInterface
{
	public function get_status(): Status
	{
		return Status::PRO_EDITION;
	}

	public function check(Status $required_status = Status::PRO_EDITION): bool
	{
		return $required_status === Status::FREE_EDITION || $required_status === Status::SUPPORTER_EDITION || $required_status === Status::PRO_EDITION;
	}

	public function is_supporter(): bool
	{
		return true;
	}

	public function is_pro(): bool
	{
		return true;
	}

	public function is_signature(): bool
	{
		return false;
	}

	public function authorize(Status $required_status = Status::PRO_EDITION): void
	{
		if (!$this->check($required_status)) {
			throw new SupporterOnlyOperationException($required_status);
		}
	}

	public function when(mixed $valIfTrue, mixed $valIfFalse, Status $required_status = Status::PRO_EDITION): mixed
	{
		$retValue = $this->check($required_status) ? $valIfTrue : $valIfFalse;

		return is_callable($retValue) ? $retValue() : $retValue;
	}

	public function validate(): bool
	{
		return true;
	}
}
