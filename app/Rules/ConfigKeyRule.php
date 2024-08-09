<?php

namespace App\Rules;

use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Configs;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ConfigKeyRule implements DataAwareRule, ValidationRule
{
	use ValidateTrait;

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
	public function passes(string $attribute, mixed $value): bool
	{
		$path = explode('.', $attribute);
		if (count($path) !== 3) {
			throw new LycheeLogicException('ConfigValueRule: attribute must be in the form of "xxx.*.value"');
		}

		$config_key = $this->data[$path[0]][intval($path[1])]['key'];

		return array_key_exists($config_key, Configs::get());
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute is not a valid configuration key.';
	}
}
