<?php

namespace App\Http\Controllers\Install;

use App\Exceptions\Internal\FrameworkException;
use App\Http\Requests\Install\SetUpAdminRequest;
use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

/**
 * Class MigrationController.
 */
class SetUpAdminController extends Controller
{
	public function init(): View
	{
		return view('install.setup-admin',
			[
				'title' => 'Lychee-installer',
				'step' => 5,
				'success' => false,
			]);
	}

	/**
	 * Migrates the Lychee DB and generates a new API key.
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 */
	public function view(SetUpAdminRequest $request): View
	{
		/** @var string|null $error */
		$error = null;
		try {
			$user = new User();
			$user->may_upload = true;
			$user->may_edit_own_settings = true;
			$user->may_administrate = true;
			$user->username = $request->username();
			$user->password = Hash::make($request->password());
			$user->save();
		} catch (\Throwable $e) {
			$error = $e->getMessage();
			$error .= '<br>' . $e->getPrevious()->getMessage();
		}

		try {
			return view('install.setup-admin', [
				'title' => 'Lychee-setup-admin',
				'step' => 5,
				'error' => $error,
				'success' => $error === null,
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}
}
