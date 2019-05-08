<?php

namespace Tests\Feature;

use App;
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

	private function git_path()
	{
		if (App::runningUnitTests()) {
			return '.git';
		}
		else {
			return '../.git';
		}
	}


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
		$this->branch = @file_get_contents(sprintf('%s/HEAD', $this->git_path()));
		$this->warning($this->branch);

		$git_info = $this->gitHubFunctions->get_info();
		$this->assertEquals('', $git_info);
	}
}
