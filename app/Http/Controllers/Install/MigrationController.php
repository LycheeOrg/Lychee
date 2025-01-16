<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Install;

use App\Actions\InstallUpdate\Pipes\ArtisanKeyGenerate;
use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use App\Actions\InstallUpdate\Pipes\ArtisanViewClear;
use App\Actions\InstallUpdate\Pipes\QueryExceptionChecker;
use App\Actions\InstallUpdate\Pipes\Spacer;
use App\Exceptions\InstallationFailedException;
use Illuminate\Contracts\View\View;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;

/**
 * Class MigrationController.
 */
class MigrationController extends Controller
{
	/**
	 * Migrates the Lychee DB and generates a new API key.
	 *
	 * @return View
	 */
	public function view(): View
	{
		$output = [];
		$hasErrors = false;
		try {
			$output = app(Pipeline::class)
				->send($output)
				->through([
					ArtisanViewClear::class,
					ArtisanMigrate::class,
					QueryExceptionChecker::class,
					Spacer::class,
					ArtisanKeyGenerate::class,
					Spacer::class,
				])
				->thenReturn();
		} catch (InstallationFailedException) {
			$hasErrors = true;
		}

		return view('install.migrate', [
			'title' => 'Lychee-installer',
			'step' => 4,
			'lines' => $output,
			'errors' => $hasErrors,
		]);
	}
}
