<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Actions\Update\Check as CheckUpdate;
use App\Contracts\InternalLycheeException;
use App\Contracts\LycheeException;
use App\DTO\DiagnosticInfo;
use App\Exceptions\Internal\FrameworkException;
use App\Facades\AccessControl;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class DiagnosticsController extends Controller
{
	public const ERROR_MSG = 'You must have administrator rights to see this.';

	private function isAuthorized(): bool
	{
		return AccessControl::is_admin() || AccessControl::noLogin();
	}

	/**
	 * This function return the Diagnostic data as an JSON array.
	 * should be used for AJAX request.
	 *
	 * @param Errors        $checkErrors
	 * @param Info          $collectInfo
	 * @param Configuration $config
	 * @param CheckUpdate   $checkUpdate
	 *
	 * @return DiagnosticInfo
	 *
	 * @throws InternalLycheeException
	 */
	public function get(Errors $checkErrors, Info $collectInfo, Configuration $config, CheckUpdate $checkUpdate): DiagnosticInfo
	{
		$authorized = $this->isAuthorized();

		return new DiagnosticInfo($checkErrors->get(), $authorized ? $collectInfo->get() : [self::ERROR_MSG], $authorized ? $config->get() : [self::ERROR_MSG], $checkUpdate->getCode());
	}

	/**
	 * Return the diagnostic information as a page.
	 *
	 * @return View
	 *
	 * @throws LycheeException
	 */
	public function view(Errors $checkErrors, Info $collectInfo, Configuration $config, CheckUpdate $checkUpdate): View
	{
		try {
			return view('diagnostics', $this->get($checkErrors, $collectInfo, $config, $checkUpdate));
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return string[] list of messages
	 */
	public function getSize(Space $space): array
	{
		return $this->isAuthorized() ? $space->get() : [self::ERROR_MSG];
	}
}
