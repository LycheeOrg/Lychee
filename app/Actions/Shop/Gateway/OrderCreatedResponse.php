<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Shop\Gateway;

use Omnipay\Common\Message\ResponseInterface;

class OrderCreatedResponse implements ResponseInterface
{
	public function __construct(
		public string $transaction_reference,
	) {
	}

	public function getRequest()
	{
		throw new \Exception('Not implemented');
	}

	public function isSuccessful()
	{
		return true;
	}

	public function isRedirect()
	{
		return false;
	}

	public function isCancelled()
	{
		return false;
	}

	public function getMessage()
	{
		throw new \Exception('Not implemented');
	}

	public function getCode()
	{
		throw new \Exception('Not implemented');
	}

	public function getTransactionReference()
	{
		return $this->transaction_reference;
	}

	public function getData()
	{
		throw new \Exception('Not implemented');
	}

	public function send(): self
	{
		return $this;
	}
}