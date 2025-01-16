<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Rules;

use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Configs;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;

class ConfigValueRule implements DataAwareRule, ValidationRule
{
	use ValidateTrait;

	/** @var Collection<int,Configs> */
	private Collection $configs;

	/**
	 * All of the data under validation.
	 *
	 * @var array<string,mixed>
	 */
	protected $data = [];

	public function __construct()
	{
		$this->configs = Configs::all();
	}

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

		$template = 'Error: Expected %s, got ' . ($value ?? 'NULL') . '.';
		$array_key = $this->data[$path[0]][intval($path[1])]['key'];

		return '' === $this->configs->first(fn (Configs $c) => $c->key === $array_key)->sanity($value, $template);
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute is not a valid configuration value.';
	}
}
