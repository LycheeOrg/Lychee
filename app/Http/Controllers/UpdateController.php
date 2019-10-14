<?php

namespace App\Http\Controllers;

use App\Configs;
use App\ControllerFunctions\ApplyUpdateFunctions;
use App\Exceptions\NotInCacheException;
use App\Exceptions\NotMasterException;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
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
	 * @var ApplyUpdateFunctions
	 */
	private $applyUpdateFunctions;

	/**
	 * @var GitRequest
	 */
	private $gitRequest;

	/**
	 * @param GitHubFunctions      $gitHubFunctions
	 * @param ApplyUpdateFunctions $applyUpdateFunctions
	 * @param GitRequest           $gitRequest
	 */
	public function __construct(
		GitHubFunctions $gitHubFunctions,
		ApplyUpdateFunctions $applyUpdateFunctions,
		GitRequest $gitRequest
	) {
		$this->gitHubFunctions = $gitHubFunctions;
		$this->applyUpdateFunctions = $applyUpdateFunctions;
		$this->gitRequest = $gitRequest;
	}

	/**
	 * @return bool
	 *
	 * @throws NotMasterException
	 * @throws NotInCacheException
	 */
	private function forget_and_check()
	{
		$this->gitRequest->clear_cache();

		return $this->gitHubFunctions->is_up_to_date(false);
	}

	/**
	 * Return if up to date or the number of commits behind
	 * This invalidates the cache for the url.
	 *
	 * @return string
	 */
	public function check()
	{
		try {
			$up_to_date = $this->forget_and_check();
		} catch (Exception $e) {
			return Response::error($e->getMessage()); // Not master
		}

		// when going through the CI, we are always up to date...
		if (!$up_to_date) {
			// @codeCoverageIgnoreStart
			return Response::json($this->gitHubFunctions->get_behind_text());
		// @codeCoverageIgnoreEnd
		} else {
			return Response::json('Already up to date');
		}
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
		if (!$this->gitHubFunctions->is_usable()) {
			return Response::error('../.git (and subdirectories) are not executable, check the permissions');
		}

		// @codeCoverageIgnoreStart
		return $this->applyUpdateFunctions->apply();
	}
}
