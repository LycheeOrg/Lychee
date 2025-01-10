<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Install;

use App\Http\Requests\Install\SetUpAdminRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

/**
 * Class SetUpAdminController.
 */
class SetUpAdminController extends Controller
{
	/**
	 * Called on GET request.
	 *
	 * @return View
	 */
	public function init(): View
	{
		return view('install.setup-admin',
			[
				'title' => 'Lychee-installer',
				'step' => 5,
			]);
	}

	/**
	 * Set up the admin user.
	 * Called on POST request.
	 *
	 * @return View
	 */
	public function create(SetUpAdminRequest $request): View
	{
		$error = '';
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

		if ($error === '') {
			return view('install.setup-success', [
				'title' => 'Lychee-setup-admin',
				'step' => 5,
			]);
		}

		return view('install.setup-admin', [
			'title' => 'Lychee-setup-admin',
			'step' => 5,
			'error' => $error,
		]);
	}
}
