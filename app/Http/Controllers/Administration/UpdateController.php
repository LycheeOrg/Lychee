<?php

namespace App\Http\Controllers\Administration;

use App\ControllerFunctions\Update\Apply as ApplyUpdate;
use App\ControllerFunctions\Update\Check as CheckUpdate;
use App\Http\Controllers\Controller;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\SessionFunctions;
use App\Response;
use Exception;
use Illuminate\Http\Request;

/**
 * Class UpdateController.
 */
class UpdateController extends Controller
{
	/**
	 * @var ApplyUpdate
	 */
	private $applyUpdate;

	/**
	 * @var CheckUpdate
	 */
	private $checkUpdate;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @param GitHubFunctions $gitHubFunctions
	 * @param ApplyUpdate     $apply
	 * @param GitRequest      $gitRequest
	 */
	public function __construct(
		ApplyUpdate $applyUpdate,
		CheckUpdate $checkUpdate,
		SessionFunctions $sessionFunctions,
		LycheeVersion $lycheeVersion
	) {
		$this->applyUpdate = $applyUpdate;
		$this->checkUpdate = $checkUpdate;
		$this->sessionFunctions = $sessionFunctions;
		$this->lycheeVersion = $lycheeVersion;
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
			return Response::json($this->checkUpdate->getText());
			// @codeCoverageIgnoreStart
		} catch (Exception $e) {
			return Response::error($e->getMessage()); // Not master
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * This requires a php to have a shell access.
	 * This method execute the update (git pull).
	 *
	 * @return array|string
	 */
	public function apply()
	{
		try {
			$this->checkUpdate->canUpdate();
			// @codeCoverageIgnoreStart
		} catch (Exception $e) {
			return Response::error($e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		// @codeCoverageIgnoreStart
		return $this->applyUpdate->run();
	}

	public function force(Request $request)
	{
		if ($this->lycheeVersion->getDBVersion()['version'] >= $this->lycheeVersion->getFileVersion()['version']) {
			return redirect()->route('home');
		}

		if (
			$this->sessionFunctions->is_admin() || $this->sessionFunctions->noLogin() ||
			$this->sessionFunctions->log_as_admin($request['username'] ?? '', $request['password'] ?? '', $request->ip())
		) {
			$output = [];
			$this->applyUpdate->artisan($output);
			$this->applyUpdate->filter($output);

			return '<pre>' . implode("\n", $output) . '</pre>';
		} else {
			return view('error.update', ['code' => '403', 'message' => 'Incorrect username or password']);
		}
	}
}
