<?php

namespace Tests\Feature;

use App\Configs;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\ConfigFunctions;
use Tests\TestCase;

class GithubTest extends TestCase
{

	/**
	 * @var ConfigFunctions
	 */
	private $configFunctions;

	/**
	 * @var GitHubFunctions
	 */
	private $gitHubFunctions;



	/**
	 */
	public function __construct()
	{
		parent::__construct();

		$this->configFunctions = new ConfigFunctions();
		$this->gitHubFunctions = new GitHubFunctions();
	}

	public function test_github()
	{
		$git_info = $this->gitHubFunctions->get_info();
		$this->assertEquals('', $git_info);
	}
}
