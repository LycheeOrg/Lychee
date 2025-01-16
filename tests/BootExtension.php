<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace Tests;

use PHPUnit\Runner\Extension\Extension as PhpunitExtension;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class BootExtension implements PhpunitExtension
{
	public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
	{
		$facade->registerSubscriber(
			new LoadedSubscriber()
		);
	}
}