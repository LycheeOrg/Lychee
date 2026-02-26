<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\Renamer;

use App\Enum\RenamerModeType;
use App\Models\RenamerRule;
use App\Repositories\ConfigManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LycheeVerify\Contract\VerifyInterface;
use function Safe\preg_replace;

/**
 * Class Renamer.
 *
 * Handles the application of renamer rules to strings.
 *
 * This class provides functionality to transform strings (typically file names or titles)
 * based on a set of predefined rules associated with a user. Rules are applied in order
 * of their 'order' field, and each rule can operate in one of three modes:
 *
 * - FIRST: Replace only the first occurrence of the needle.
 * - ALL: Replace all occurrences of the needle.
 * - REGEX: Use regular expressions for matching and replacing patterns.
 *
 * The renamer can be enabled or disabled globally through configuration settings.
 * There's also an 'enforced' mode where rules from a specific user (typically the admin)
 * are applied regardless of the current user's settings.
 *
 * Usage example:
 * ```php
 * $renamer = new Renamer($user_id);
 * $transformed = $renamer->handle('Original string');
 * ```
 *
 * @see RenamerRule For the model that defines individual rules
 * @see RenamerModeType For the available replacement modes
 */
class Renamer
{
	/**
	 * @var Collection<int,RenamerRule> Colection of RenamerRule models
	 */
	private Collection $rules;

	public readonly bool $is_enabled;

	/**
	 * Constructor fetches the Renamer rules for the current user.
	 *
	 * The constructor checks if the renamer functionality is enabled globally
	 * and if the current user has the necessary permissions to use it.
	 *
	 * If renamer enforcement is enabled in the configuration, it will override
	 * the user ID with the system owner's ID, ensuring consistent renaming rules
	 * are applied throughout the system.
	 *
	 * @param int        $user_id  The ID of the user whose rules should be applied
	 * @param bool|null  $is_photo Whether to include photo rules (default: null)
	 * @param bool|null  $is_album Whether to include album rules (default: null)
	 * @param int[]|null $rule_ids When provided, only apply rules whose IDs are in this array
	 */
	public function __construct(
		int $user_id,
		?bool $is_photo = null,
		?bool $is_album = null,
		?array $rule_ids = null)
	{
		$verify = app(VerifyInterface::class);
		$config_manager = app(ConfigManager::class);

		$renamer_enabled = $config_manager->getValueAsBool('renamer_enabled');
		$this->is_enabled = $renamer_enabled && $verify->is_supporter();

		$enforced = $config_manager->getValueAsBool('renamer_enforced');
		$before = $config_manager->getValueAsBool('renamer_enforced_before');
		$after = $config_manager->getValueAsBool('renamer_enforced_after');

		$user_rules = RenamerRule::query()
			->where('owner_id', $user_id)
			->when($is_photo !== null, fn ($query) => $query->where('is_photo_rule', $is_photo))
			->when($is_album !== null, fn ($query) => $query->where('is_album_rule', $is_album))
			->where('is_enabled', true)
			->orderBy('order', 'asc')
			->get();

		$owner_rules = RenamerRule::query()
			->where('owner_id', $config_manager->getValueAsInt('owner_id'))
			->when($is_photo !== null, fn ($query) => $query->where('is_photo_rule', $is_photo))
			->when($is_album !== null, fn ($query) => $query->where('is_album_rule', $is_album))
			->where('is_enabled', true)
			->orderBy('order', 'asc')
			->get();

		// Enforce renamer rules if configured
		// This is useful for users who want to ensure renaming is always applied
		// regardless of the user's settings. MOUHAHAHAHA!
		if ($enforced) {
			$this->rules = $owner_rules;

			return;
		}

		// Start with an empty collection
		$rules = collect();
		if ($before) {
			$rules = $rules->merge($owner_rules);
		}
		$rules = $rules->merge($user_rules);
		// Only merge owner rules after user rules if configured
		// That way we avoid double application of rules
		if ($after && $user_rules->isNotEmpty()) {
			$rules = $rules->merge($owner_rules);
		}

		$this->rules = $rules;

		// Filter rules by explicit IDs when provided
		if ($rule_ids !== null) {
			$this->rules = $this->rules->filter(fn (RenamerRule $rule) => in_array($rule->id, $rule_ids, true));
		}
	}

	/**
	 * Return the collection of renamer rules.
	 *
	 * This method provides access to the rules that were loaded for the current user.
	 * Rules are already filtered to only include enabled ones and are sorted by order.
	 *
	 * @return Collection<int,RenamerRule> The collection of active renamer rules
	 */
	public function getRules(): Collection
	{
		return $this->rules;
	}

	/**
	 * Applies all renamer rules to the input string and returns the result.
	 *
	 * This method iterates through all enabled rules in their specified order,
	 * applying each one sequentially to the input string. The result of each rule
	 * application becomes the input for the next rule.
	 *
	 * If the renamer is not enabled (either globally or for the current user),
	 * the input string is returned unchanged.
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
	 * Apply renamer rules to an array of inputs.
	 *
	 * This method processes each input string through the renamer rules,
	 * returning an array of transformed strings.
	 *
	 * @param string[] $inputs
	 *
	 * @return string[]
	 */
	public function handleMany(array $inputs): array
	{
		if (!$this->is_enabled) {
			// If renamer is not enabled, return the inputs unchanged
			return $inputs;
		}

		return array_map(fn ($input) => $this->handle($input), $inputs);
	}

	/**
	 * Apply a single renamer rule to the input string.
	 *
	 * This method selects the appropriate replacement strategy based on the rule's mode:
	 * - FIRST: Replace only the first occurrence of the needle using the replaceFirst method
	 * - ALL: Replace all occurrences of the needle using PHP's str_replace function
	 * - REGEX: Apply regular expression replacement using preg_replace
	 *
	 * Any exceptions during rule application (particularly with regex patterns) are caught,
	 * logged, and the original input is returned unchanged to ensure stability.
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

				// Trim whitespace from the beginning and end of the string
				RenamerModeType::TRIM => trim($input),

				// Convert the string to lowercase
				RenamerModeType::LOWER => mb_convert_case($input, MB_CASE_LOWER),

				// Convert the string to uppercase
				RenamerModeType::UPPER => mb_convert_case($input, MB_CASE_UPPER),

				// Capitalize the first letter of each word in the string
				RenamerModeType::UCWORDS => mb_convert_case($input, MB_CASE_TITLE),

				// Capitalize the first letter of the string
				RenamerModeType::UCFIRST => mb_strtoupper(mb_substr($input, 0, 1)) . mb_substr($input, 1),
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
	 * This method performs a targeted replacement of just the first occurrence of
	 * the needle string within the haystack. It's more efficient than using a regular
	 * expression when we only need to replace the first match.
	 *
	 * The method uses strpos to find the position of the first occurrence,
	 * then uses substr_replace to perform the actual replacement. If the needle
	 * is not found, the original string is returned unchanged.
	 *
	 * @param string $needle      The string to find
	 * @param string $replacement The replacement string
	 * @param string $haystack    The string to search in
	 *
	 * @return string The resulting string after replacing the first occurrence
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
