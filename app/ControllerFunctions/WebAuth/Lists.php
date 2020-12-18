<?php

namespace App\ControllerFunctions\WebAuth;

use App\ModelFunctions\SessionFunctions;

class Lists
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
		return $this->sessionFunctions->user()->webAuthnCredentials->map(fn ($cred) => ['id' => $cred->id]);
	}
}
