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
	public function __construct(
		private RequirementsChecker $requirements,
		private DefaultConfig $config,
	) {
	}

	/**
	 * Display the requirements page.
	 */
	public function view(): View
	{
		$php_support_info = $this->requirements->checkPHPVersion(
			$this->config->get_core()['minPhpVersion']
		);
		$reqs = $this->requirements->check(
			$this->config->get_requirements()
		);

		return view('install.requirements', [
			'title' => 'Lychee-installer',
			'step' => 1,
			'phpSupportInfo' => $php_support_info,
			'requirements' => $reqs['requirements'],
			'errors' => $reqs['errors'] ?? null,
		]);
	}
}