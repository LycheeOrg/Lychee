<?php

namespace App\ControllerFunctions\WebAuth;

use App\ModelFunctions\SessionFunctions;

class Delete
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

	public function do($ids)
	{
		$user = $this->sessionFunctions->user();
		$user->removeCredential($ids);

		return 'true';
	}
}
