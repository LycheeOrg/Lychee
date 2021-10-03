<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Actions\Update\Check as CheckUpdate;
use App\Contracts\LycheeException;
use App\Facades\AccessControl;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;

class DiagnosticsController extends Controller
{
	/**
	 * Return the requested information.
	 *
	 * @return array
	 *
	 * @throws LycheeException
	 */
	private function get_data(): array
	{
		$errors = resolve(Errors::class)->get();

		if (AccessControl::is_admin() || AccessControl::noLogin()) {
			$infos = resolve(Info::class)->get();
			$configs = resolve(Configuration::class)->get();
		} else {
			$infos = ['You must be logged in to see this.'];
			$configs = ['You must be logged in to see this.'];
		}

		return [
			'errors' => $errors,
			'infos' => $infos,
			'configs' => $configs,
		];
	}

	/**
	 * This function return the Diagnostic data as an JSON array.
	 * should be used for AJAX request.
	 *
	 * @param CheckUpdate $checkUpdate
	 *
	 * @return array
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
	 * @throws BindingResolutionException
	 * @throws LycheeException
	 */
	public function show(): View
	{
		return view('diagnostics', $this->get_data());
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return array
	 */
	public function get_size(): array
	{
		$infos = ['You must be logged in to see this.'];
		if (AccessControl::is_admin() || AccessControl::noLogin()) {
			$infos = resolve(Space::class)->get();
		}

		return $infos;
	}
}
