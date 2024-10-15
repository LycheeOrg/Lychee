<?php

namespace App\Rules;

use App\Models\Configs;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use LycheeVerify\Contract\VerifyInterface;

class ConfigKeyRequireSupportRule implements DataAwareRule, ValidationRule
{
	use ValidateTrait;

	protected VerifyInterface $verify;

	public function __construct(VerifyInterface $verify)
	{
		$this->verify = $verify;
	}

	/**
	 * All of the data under validation.
	 *
	 * @var array<string,mixed>
	 */
	protected $data = [];

	/**
	 * Set the data under validation.
	 *
	 * @param array<string,mixed> $data
	 *
	 * @phpstan-ignore-next-line
	 */
	public function setData(array $data): static
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (is_string($value) === false) {
			$fail($attribute . ' is not a string');

			return;
		}

		/** @var string $value */
		if (!array_key_exists($value, Configs::get())) {
			// This is taken care of in ConfigKeyRule
			return;
		}

		/** @var string $value */
		$config = Configs::where('key', '=', $value)->firstOrFail();
		if ($config->level === 1 && !$this->verify->is_supporter()) {
			$fail('Error: This functionality is only available in the Supporter Edition of Lychee. See here: https://lycheeorg.github.io/get-supporter-edition/');

			return;
		}
	}
}
