<?php

namespace App\Http\Controllers\Install;

use App\ControllerFunctions\Install\DefaultConfig;
use App\ControllerFunctions\Install\RequirementsChecker;
use App\Http\Controllers\Controller;

final class RequirementsController extends Controller
{
	/**
	 * @var RequirementsChecker
	 */
	protected $requirements;
	/**
	 * @var DefaultConfig
	 */
	protected $config;

	/**
	 * @param RequirementsChecker $checker
	 * @param Config              $config
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
	public function view()
	{
		$phpSupportInfo = $this->requirements->checkPHPversion(
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