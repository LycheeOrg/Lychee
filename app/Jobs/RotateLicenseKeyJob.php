<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Contract\Status;
use LycheeVerify\Rotation;
use LycheeVerify\Verify;

class RotateLicenseKeyJob
{
	use Dispatchable;

	public function handle(Verify $verify, Rotation $rotation): void
	{
		if (!Schema::hasTable('configs')) {
			return;
		}

		if ($verify->get_status() !== Status::FREE_EDITION) {
			return;
		}

		$license_key = DB::table('configs')->where('key', 'license_key')->first()?->value ?? '';
		if ($license_key === '') {
			return;
		}

		/** @var string $api_key */
		$api_key = config('verify.keygen_api_key', '');
		if ($api_key === '') {
			return;
		}

		$result = $rotation->rotate();

		if ($result->success) {
			$verify->reset_status();
		}
	}
}
