<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\HoneyPot\DefaultNotFound;
use App\Actions\HoneyPot\EnvAccessTentative;
use App\Actions\HoneyPot\FlaggedPathsAccessTentative;
use App\Actions\HoneyPot\HoneyIsActive;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;

/**
 * This is a HoneyPot. We use this to allow Fail2Ban to stop scanning.
 * The goal is pretty simple, if you are hitting this controller, and touch the honey,
 * then this means that you have no interest in our pictures.
 */
class HoneyPotController extends Controller
{
	/**
	 * The array of class pipes.
	 *
	 * @var array<int,class-string>
	 */
	private array $pipes = [
		HoneyIsActive::class,
		EnvAccessTentative::class,
		FlaggedPathsAccessTentative::class,
		DefaultNotFound::class,
	];

	public function __invoke(string $path = ''): void
	{
		app(Pipeline::class)
			->send($path)
			->through($this->pipes)
			->thenReturn();
	}
}
