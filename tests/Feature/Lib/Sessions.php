<?php

namespace Tests\Feature;

use App\ModelFunctions\SessionFunctions;

class Sessions
{
	/**
	 * @var SessionFunctions
	 */
	public $sessionFunctions;

	public function __construct()
	{
		$this->sessionFunctions = new SessionFunctions();
	}

	public function log_as_admin()
	{
		$this->sessionFunctions->log_as_id(0);
	}

	public function logout()
	{
		$this->sessionFunctions->logout();
	}
}