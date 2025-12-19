<?php

namespace App\Actions\Shop\Gateway;

use Omnipay\Common\Message\ResponseInterface;

class OrderFailedResponse implements ResponseInterface
{
	private string $message;

	public function __construct(
		array $details,
	) {
		$error_details = $details['details'][0] ?? null;
		$this->message = $error_details !== null
			? ($error_details['issue'] . ' ' . $error_details['description'] . ' (' . ($this->details['debug_id'] ?? '') . ')') :
			($details['error'] ?? 'Unknown error');
	}

	public function getRequest()
	{
		throw new \Exception('Not implemented');
	}

	public function isSuccessful()
	{
		return false;
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
		return $this->message;
	}

	public function getCode()
	{
		throw new \Exception('Not implemented');
	}

	public function getTransactionReference()
	{
		throw new \Exception('Not implemented');
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