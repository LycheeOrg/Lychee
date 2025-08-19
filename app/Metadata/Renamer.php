<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata;

use App\Enum\RenamerModeType;
use App\Models\Configs;
use App\Models\RenamerRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LycheeVerify\Verify;

use function Safe\preg_replace;

/**
 * Class Renamer.
 *
 * Handles the application of renamer rules to strings.
 */
final class Renamer
{
	/**
	 * @var Collection<int,RenamerRule> Colection of RenamerRule models
	 */
	private Collection $rules;

	public readonly bool $is_enabled;

	/**
	 * Constructor fetches the Renamer rules for the current user.
	 */
	public function __construct(int $user_id)
	{
		$verifier = resolve(Verify::class);
		$renamer_enabled = Configs::getValueAsBool('renamer_enabled');
		$this->is_enabled = $renamer_enabled && $verifier->is_supporter();

		// Enforce renamer rules if configured
		// This is useful for users who want to ensure renaming is always applied
		// regardless of the user's settings. MOUHAHAHAHA!
		if (Configs::getValueAsBool('renamer_enforced')) {
			$user_id = Configs::getValueAsInt('owner_id');
		}

		// Fetch rules for this user, ordered by the 'order' column ASC
		$this->rules = RenamerRule::query()
			->where('owner_id', $user_id)
			->where('is_enabled', true)
			->orderBy('order', 'asc')
			->get();
	}

	/**
	 * Return the collection of renamer rules.
	 *
	 * @return Collection<int,RenamerRule>
	 */
	public function getRules(): Collection
	{
		return $this->rules;
	}

	/**
	 * Applies all renamer rules to the input string and returns the result.
	 *
	 * @param string $input The input string to be processed
	 *
	 * @return string The processed string after applying renamer rules
	 */
	public function handle(string $input): string
	{
		if (!$this->is_enabled) {
			// If renamer is not enabled, return the input unchanged
			return $input;
		}

		$result = $input;

		// Apply each rule in the defined order
		foreach ($this->rules as $rule) {
			$result = $this->applyRule($result, $rule);
		}

		return $result;
	}

	/**
	 * Apply a single renamer rule to the input string.
	 *
	 * @param string      $input The input string
	 * @param RenamerRule $rule  The rule to apply
	 *
	 * @return string The string after applying the rule
	 */
	private function applyRule(string $input, RenamerRule $rule): string
	{
		try {
			return match ($rule->mode) {
				// Replace only the first occurrence of the needle
				RenamerModeType::FIRST => $this->replaceFirst($rule->needle, $rule->replacement, $input),

				// Replace all occurrences of the needle
				RenamerModeType::ALL => str_replace($rule->needle, $rule->replacement, $input),

				// Use regular expression for replacement
				RenamerModeType::REGEX => preg_replace($rule->needle, $rule->replacement, $input),
			};
		} catch (\Exception $e) {
			// Handle any exceptions that may occur during the replacement
			Log::error('Renamer rule application failed', [
				'input' => $input,
				'rule' => $rule,
				'error' => $e->getMessage(),
			]);

			// We just return the input unchanged
			return $input;
		}
	}

	/**
	 * Replace only the first occurrence of the needle in the haystack.
	 *
	 * @param string $haystack    The string to search in
	 * @param string $needle      The string to find
	 * @param string $replacement The replacement string
	 *
	 * @return string The resulting string
	 */
	private function replaceFirst(string $needle, string $replacement, string $haystack): string
	{
		$pos = strpos($haystack, $needle);
		if ($pos !== false) {
			return substr_replace($haystack, $replacement, $pos, strlen($needle));
		}

		return $haystack;
	}
}
