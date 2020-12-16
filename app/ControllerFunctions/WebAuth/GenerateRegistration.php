<?php

namespace App\ControllerFunctions\WebAuth;

use App\ModelFunctions\SessionFunctions;
use App\Models\User;
use DarkGhostHunter\Larapass\Facades\WebAuthn;

class GenerateRegistration
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

	public function do()
	{
		/**
		 * @var User
		 */
		$user = $this->sessionFunctions->user();

		// Create an attestation for a given user.
		return WebAuthn::generateAttestation($user);
	}
}
