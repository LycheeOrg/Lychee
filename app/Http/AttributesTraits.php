<?php

namespace App\Http;

use App\Exceptions\Internal\LycheeLogicException;
use App\Repositories\ConfigManager;
use LycheeVerify\Contract\Status;
use LycheeVerify\Contract\VerifyInterface;

trait AttributesTraits
{
	public function verify(): VerifyInterface
	{
		if (!$this->attributes->has('verify')) {
			throw new LycheeLogicException('request attribute "verify" is not set.');
		}

		$verify = $this->attributes->get('verify');
		if ($verify instanceof VerifyInterface) {
			return $verify;
		}
		throw new LycheeLogicException('request attribute "verify" is set but not an instance of VerifyInterface.');
	}

	public function get_status(): Status
	{
		if (!$this->attributes->has('status')) {
			throw new LycheeLogicException('request attribute "status" is not set.');
		}

		$status = $this->attributes->get('status');
		if ($status instanceof status) {
			return $status;
		}
		throw new LycheeLogicException('request attribute "status" is set but not an instance of Status.');
	}

	public function configs(): ConfigManager
	{
		if (!$this->attributes->has('configs')) {
			throw new LycheeLogicException('Request attribute "configs" is not set.');
		}

		$configs = $this->attributes->get('configs');
		if ($configs instanceof ConfigManager) {
			return $configs;
		}
		throw new LycheeLogicException('request attribute "configs" is set but not an instance of ConfigManager.');
	}
}