<?php

namespace Installer\Controller;

use Installer\Config;
use Installer\Helpers\RequirementsChecker;

final class RequirementsController implements Controller
{
	/**
	 * @var RequirementsChecker
	 */
	protected $requirements;
	/**
	 * @var Config
	 */
	protected $config;



	/**
	 * @param  RequirementsChecker  $checker
	 * @param  Config  $config
	 */
	public function __construct(RequirementsChecker $checker, Config $config)
	{
		$this->requirements = $checker;
		$this->config = $config;
	}



	/**
	 * Display the requirements page.
	 */
	public function do()
	{
		$phpSupportInfo = $this->requirements->checkPHPversion(
			$this->config->get_core()['minPhpVersion']
		);
		$requirements = $this->requirements->check(
			$this->config->get_requirements()
		);

		return ['phpSupportInfo' => $phpSupportInfo, 'requirements' => $requirements];
	}



	/**
	 * @return string
	 */
	public function view()
	{
		return 'Requirements';
	}
}