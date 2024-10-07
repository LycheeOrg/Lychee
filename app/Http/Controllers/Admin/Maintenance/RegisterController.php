<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\RegisterRequest;
use App\Http\Resources\GalleryConfigs\RegisterData;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use LycheeVerify\Verify;

class RegisterController extends Controller
{
	/**
	 * @param RegisterRequest $request
	 *
	 * @return RegisterData
	 */
	public function __invoke(RegisterRequest $request): RegisterData
	{
		Configs::set('license_key', $request->key()->getValue());
		$verify = resolve(Verify::class);
		$is_supporter = $verify->is_supporter();
		if ($is_supporter) {
			return new RegisterData(true);
		}

		// Not valid, reset the key.
		Configs::set('license_key', '');

		return new RegisterData(false);
	}
}
