<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Install;

use App\Actions\InstallUpdate\DefaultConfig;
use App\Actions\InstallUpdate\RequirementsChecker;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class RequirementsController extends Controller
{
	private RequirementsChecker $requirements;
	private DefaultConfig $config;

	/**
	 * @param RequirementsChecker $checker
	 * @param DefaultConfig       $config
	 */
	public function __construct(RequirementsChecker $checker, DefaultConfig $config)
	{
		$this->requirements = $checker;
		$this->config = $config;
	}

	/**
	 * Display the requirements page.
	 *
	 * @return View
	 */
	public function view(): View
	{
		$phpSupportInfo = $this->requirements->checkPHPVersion(
			$this->config->get_core()['minPhpVersion']
		);
		$reqs = $this->requirements->check(
			$this->config->get_requirements()
		);

		return view('install.requirements', [
			'title' => 'Lychee-installer',
			'step' => 1,
			'phpSupportInfo' => $phpSupportInfo,
			'requirements' => $reqs['requirements'],
			'errors' => $reqs['errors'] ?? null,
		]);
	}
}
