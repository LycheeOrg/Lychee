<?php

namespace App\Http\Controllers;

use App\Configs;
use App\ControllerFunctions\UpdateFunctions;
use App\Metadata\GitHubFunctions;
use App\Response;
use Exception;

/**
 * Class UpdateController.
 */
class UpdateController extends Controller
{
	/**
	 * @var GitHubFunctions
	 */
	private $gitHubFunctions;

	/**
	 * @var UpdateFunctions
	 */
	private $updateFunctions;

	/**
	 * @param GitHubFunctions $gitHubFunctions
	 * @param UpdateFunctions $updateFunctions
	 */
	public function __construct(GitHubFunctions $gitHubFunctions, UpdateFunctions $updateFunctions)
	{
		$this->gitHubFunctions = $gitHubFunctions;
		$this->updateFunctions = $updateFunctions;
	}

	/**
	 * This requires a php to have a shell access.
	 * This method execute the update (git pull).
	 *
	 * @return array|string
	 */
	public function do()
	{
		if (Configs::get_value('allow_online_git_pull', '0') == '0') {
			return Response::error('Online updates are not allowed.');
		}

		// When going with the CI, .git is always executable and exec is also available
		// @codeCoverageIgnoreStart
		if (!function_exists('exec')) {
			return Response::error('exec is not available');
		}
		if (!is_executable(base_path('.git'))) {
			return Response::error('../.git is not executable, check the permissions');
		}
		// @codeCoverageIgnoreEnd

		try {
			$up_to_date = $this->gitHubFunctions->is_up_to_date();
		} catch (Exception $e) {
			return Response::error($e->getMessage());
		}

		// when going through the CI, we are always up to date...
		if (!$up_to_date) {
			// @codeCoverageIgnoreStart
			return $this->updateFunctions->apply();
		// @codeCoverageIgnoreEnd
		} else {
			return Response::json('Already up to date');
		}
	}
}
