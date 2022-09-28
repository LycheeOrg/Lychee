<?php

namespace App\Http\Requests\User\Self;

class SetEmailRequest extends AbstractSelfEditRequest
{
	public const EMAIL_ATTRIBUTE = 'email';

	protected ?string $email = null;

	public function rules(): array
	{
		return [
			self::EMAIL_ATTRIBUTE => 'present|nullable|email:rfc,dns|max:100',
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->email = $values[self::EMAIL_ATTRIBUTE];
	}

	public function email(): ?string
	{
		return $this->email;
	}
}
