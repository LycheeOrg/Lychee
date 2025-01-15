<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Traits;

use LycheeVerify\Contract\VerifyInterface;
use Tests\Constants\FreeVerifyier;
use Tests\Constants\SupporterVerifyier;

trait RequireSupport
{
	final protected function getFree(): VerifyInterface
	{
		return new FreeVerifyier();
	}

	final protected function getSupporter(): VerifyInterface
	{
		return new SupporterVerifyier();
	}
}
