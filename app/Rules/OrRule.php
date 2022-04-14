<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OrRule implements Rule
{
	/**
	 * The underlying base rules.
	 *
	 * @var Rule[]
	 */
	protected array $rules;

	/**
	 * @param Rule[] $rules the underlying base rules
	 */
	public function __construct(array $rules)
	{
		$this->rules = $rules;
	}

	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		$valid = false;
		$num = sizeof($this->rules);
		if ($num === 0) {
			// Special rule: OR of an empty set of predicates is true.
			$valid = true;
		} else {
			$idx = 0;
			do {
				$valid |= $this->rules[$idx]->passes($attribute, $value);
				$idx++;
			} while (!$valid && $idx < $num);
		}

		return $valid;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute invalid';
	}
}
