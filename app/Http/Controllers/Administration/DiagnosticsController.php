<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Actions\Update\Check as CheckUpdate;
use App\Contracts\LycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Facades\AccessControl;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;

class DiagnosticsController extends Controller
{
	public const ERROR_MSG = 'You must be logged in to see this.';
	private bool $isAuthorized;

	public function __construct()
	{
		$this->isAuthorized = AccessControl::is_admin() || AccessControl::noLogin();
	}

	/**
	 * Return the requested information.
	 *
	 * @return array{errors: string[], infos: string[], configs: string[]}
	 *
	 * @throws LycheeException
	 */
	private function get_data(): array
	{
		try {
			$errors = resolve(Errors::class);
			$infos = resolve(Info::class);
			$configs = resolve(Configuration::class);

			return [
				'errors' => $errors->get(),
				'infos' => $this->isAuthorized ? $infos->get() : [self::ERROR_MSG],
				'configs' => $this->isAuthorized ? $configs->get() : [self::ERROR_MSG],
			];
		} catch (ContainerExceptionInterface $e) {
			throw new FrameworkException('could not resolve classes', $e);
		}
	}

	/**
	 * This function return the Diagnostic data as an JSON array.
	 * should be used for AJAX request.
	 *
	 * @param CheckUpdate $checkUpdate
	 *
	 * @return array{errors: string[], infos: string[], configs: string[], update: int}
	 *
	 * @throws LycheeException
	 */
	public function get(CheckUpdate $checkUpdate): array
	{
		$ret = $this->get_data();
		$ret['update'] = $checkUpdate->getCode();

		return $ret;
	}

	/**
	 * Return the diagnostic information as a page.
	 *
	 * @return View
	 *
	 * @throws LycheeException
	 */
	public function show(): View
	{
		try {
			return view('diagnostics', $this->get_data());
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('could not generate view', $e);
		}
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return string[] list of messages
	 */
	public function get_size(Space $space): array
	{
		return $this->isAuthorized ? $space->get() : [self::ERROR_MSG];
	}
}
