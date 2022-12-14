<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Actions\InstallUpdate\CheckUpdate;
use App\Contracts\LycheeException;
use App\DTO\DiagnosticInfo;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\ModelDBException;
use App\Legacy\AdminAuthentication;
use App\Policies\UserPolicy;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class DiagnosticsController extends Controller
{
	public const ERROR_MSG = 'You must have administrator rights to see this.';

	/**
	 * @throws ModelDBException
	 */
	private function isAuthorized(): bool
	{
		return Gate::check(UserPolicy::IS_ADMIN) || AdminAuthentication::isAdminNotRegistered();
	}

	/**
	 * This function return the Diagnostic data as an JSON array.
	 * should be used for AJAX request.
	 *
	 * @return DiagnosticInfo
	 *
	 * @throws LycheeException
	 * @throws InvalidTimeZoneException
	 */
	public function get(): DiagnosticInfo
	{
		$collectErrors = resolve(Errors::class);
		$collectInfo = resolve(Info::class);
		$collectConfig = resolve(Configuration::class);
		$checkUpdate = resolve(CheckUpdate::class);

		$authorized = $this->isAuthorized();

		$errors = $collectErrors->get(config('app.skip_diagnostics_checks'));
		$infos = $authorized ? $collectInfo->get() : [self::ERROR_MSG];
		$configs = $authorized ? $collectConfig->get() : [self::ERROR_MSG];

		return new DiagnosticInfo($errors, $infos, $configs, $checkUpdate->getCode());
	}

	/**
	 * Return the diagnostic information as a page.
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 * @throws InvalidTimeZoneException
	 * @throws LycheeException
	 */
	public function view(): View
	{
		try {
			return view('diagnostics', $this->get());
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return string[] list of messages
	 *
	 * @throws ModelDBException
	 */
	public function getSize(Space $space): array
	{
		return $this->isAuthorized() ? $space->get() : [self::ERROR_MSG];
	}
}
