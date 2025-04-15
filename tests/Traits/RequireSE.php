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

use Illuminate\Support\Facades\App;
use LycheeVerify\Contract\Status;
use LycheeVerify\Verify;

/**
 * Some of our tests require that the user is a subscriber.
 */
trait RequireSE
{
	protected function requireSe(Status $status = Status::SUPPORTER_EDITION): Verify
	{
		$fake_verify = new class($status) extends Verify {
			public function __construct(private Status $status)
			{
				// We need to call the parent constructor with at least those two parameters so that no query to the DB are done.
				parent::__construct(config_email: '', license_key: '');
			}

			public function get_status(): Status
			{
				return $this->status;
			}

			public function validate(): bool
			{
				return app()->runningUnitTests();
			}
		};

		App::instance(Verify::class, $fake_verify);

		return $fake_verify;
	}

	protected function resetSe(): Verify
	{
		$verify = new Verify();
		App::instance(Verify::class, $verify);

		return $verify;
	}
}
