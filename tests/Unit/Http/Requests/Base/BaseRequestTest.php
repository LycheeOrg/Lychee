<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Base;

use Illuminate\Support\Facades\App;
use LycheeVerify\Contract\VerifyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\AbstractTestCase;

class BaseRequestTest extends AbstractTestCase
{
	/** @var MockObject&VerifyInterface */
	protected MockObject $mock_verify;

	protected function setUp(): void
	{
		parent::setUp();
		$this->mock_verify = $this->createMock(VerifyInterface::class);
		App::instance(VerifyInterface::class, $this->mock_verify); // VerifyInterface is talking to DB & that is not needed for Request classes
	}
}