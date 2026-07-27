<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Install;

use App\Actions\User\Create;
use App\Enum\UserUploadTrustLevel;
use App\Http\Requests\Install\SetUpAdminRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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
	public function create(SetUpAdminRequest $request, Create $create_user): View
	{
		$error = '';
		try {
			$user = $create_user->do(
				$request->username(),
				$request->password(),
				null,
				true,
				true,
				true,
				null,
				null,
				UserUploadTrustLevel::TRUSTED,
			);
			DB::table('configs')->where('key', '=', 'owner_id')->update(['value' => $user->id]);
		} catch (\Throwable $e) {
			$error = $e->getMessage();
			$error .= '<br>' . $e->getPrevious()?->getMessage() ?? '';
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
