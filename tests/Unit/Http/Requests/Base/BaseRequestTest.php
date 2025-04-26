<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Base;

use LycheeVerify\Contract\Status;
use LycheeVerify\Verify;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSE;

class BaseRequestTest extends AbstractTestCase
{
	use RequireSE;

	protected Verify $mock_verify;

	protected function setUp(): void
	{
		parent::setUp();
		$this->mock_verify = $this->requireSe(Status::FREE_EDITION);
	}
}