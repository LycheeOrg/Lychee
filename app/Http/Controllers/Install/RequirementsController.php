<?php

namespace App\Http\Controllers\Install;

use App\Actions\Install\DefaultConfig;
use App\Actions\Install\RequirementsChecker;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
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
	 *
	 * @throws FrameworkException
	 */
	public function view(): View
	{
		try {
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
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}
}
