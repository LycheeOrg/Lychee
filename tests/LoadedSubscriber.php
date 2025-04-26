<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests;

use App\Models\User;
use PHPUnit\Event\TestSuite\Loaded;
use PHPUnit\Event\TestSuite\LoadedSubscriber as LoadedSubscriberInterface;

final class LoadedSubscriber implements LoadedSubscriberInterface
{
	use CreatesApplication;
	use MigrateApplication;

	public function notify(Loaded $event): void
	{
		$this->createApplication();
		$this->migrateApplication();

		// If there are any users in the DB, this tends to crash some tests (because we check exact count of users).
		if (User::query()->count() > 0) {
			User::truncate();
		}

		return;
	}
}