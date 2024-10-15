<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use LycheeVerify\Contract\VerifyInterface;

/**
 * This rule is designed specifically to avoid path injection.
 */
class IntegerRequireSupportRule implements ValidationRule
{
	protected VerifyInterface $verify;
	protected int $expected;

	public function __construct(int $expected, VerifyInterface $verify)
	{
		$this->verify = $verify;
		$this->expected = $expected;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(string $attribute, mixed $value, \Closure $fail): void
	{
		if (is_int($value) && intval($value) === $this->expected) {
			return;
		}

		if ($this->verify->is_supporter()) {
			return;
		}

		$fail('Error: This functionality is only available in the Supporter Edition of Lychee. See here: https://lycheeorg.github.io/get-supporter-edition/');
	}
}
