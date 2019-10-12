<?php

namespace App\Http\Controllers;

use App\Configs;
use App\ControllerFunctions\ApplyUpdateFunctions;
use App\Metadata\GitHubFunctions;
use App\Response;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

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
	 * @param GitHubFunctions      $gitHubFunctions
	 * @param ApplyUpdateFunctions $applyUpdateFunctions
	 */
	public function __construct(GitHubFunctions $gitHubFunctions, ApplyUpdateFunctions $applyUpdateFunctions)
	{
		$this->gitHubFunctions = $gitHubFunctions;
		$this->applyUpdateFunctions = $applyUpdateFunctions;
	}

	/**
	 * @return bool
	 *
	 * @throws Exception
	 */
	private function forget_and_check()
	{
		Cache::forget(Config::get('urls.update.git'));
		Cache::forget(Config::get('urls.update.git') . '_age');

		return $this->gitHubFunctions->is_up_to_date();
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
			return Response::error($e->getMessage());
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
		if (!is_executable(base_path('.git'))) {
			return Response::error('../.git is not executable, check the permissions');
		}
		// @codeCoverageIgnoreEnd

		try {
			$up_to_date = $this->forget_and_check();
		} catch (Exception $e) {
			return Response::error($e->getMessage());
		}

		// when going through the CI, we are always up to date...
		if (!$up_to_date) {
			// @codeCoverageIgnoreStart
			return $this->applyUpdateFunctions->apply();
		// @codeCoverageIgnoreEnd
		} else {
			return Response::json('Already up to date');
		}
	}
}
