<?php

namespace App\ControllerFunctions\WebAuth;

use App\ModelFunctions\SessionFunctions;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

class VerifyRegistration
{
	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(
		SessionFunctions $sessionFunctions
	) {
		$this->sessionFunctions = $sessionFunctions;
	}

	public function do($data)
	{
		/**
		 * @var User
		 */
		$user = $this->sessionFunctions->user();

		// okay.
		$credential = WebAuthn::validateAttestation($data, $user);
		if ($credential) {
			$user->addCredential($credential);

			return response()->json('Device registered!', 200);
		} else {
			return response()->json('Something went wrong with your device!', 422);
		}
	}
}
