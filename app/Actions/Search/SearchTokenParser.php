<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search;

use App\DTO\Search\SearchToken;
use Illuminate\Validation\ValidationException;
use function Safe\preg_match;
use function Safe\preg_match_all;

/**
 * Stateless parser that converts a raw (decoded) query string into an array
 * of {@link SearchToken} DTOs.
 *
 * No DB access is performed here.  Colour-name resolution and authentication
 * checks are deferred to the respective strategies.
 *
 * Grammar (simplified):
 *   query       = token ( WS token )*
 *   token       = modifier ":" sub_token | plain_term
 *   sub_token   = sub_modifier ":" op value   # rating only
 *               | op value
 *               | value
 *   op          = ">" | ">=" | "<" | "<=" | "="
 *   modifier    = "tag"|"date"|"type"|"ratio"|"color"|"colour"
 *               | "make"|"lens"|"aperture"|"iso"|"shutter"|"focal"
 *               | "title"|"description"|"location"|"model"|"rating"
 *   sub_modifier = "avg" | "own"
 */
class SearchTokenParser
{
	private const KNOWN_MODIFIERS = [
		'tag', 'date', 'type', 'ratio', 'color', 'colour',
		'make', 'lens', 'aperture', 'iso', 'shutter', 'focal',
		'title', 'description', 'location', 'model', 'rating',
	];

	/**
	 * Parse a raw decoded query string into search tokens.
	 *
	 * @param string $raw the query string after base64-decode; not LIKE-escaped
	 *
	 * @return SearchToken[]
	 *
	 * @throws ValidationException when a token is syntactically invalid
	 */
	public static function parse(string $raw): array
	{
		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}

		// Split by spaces but keep quoted strings together.
		preg_match_all('/"[^"]*"|\S+/', $raw, $matches);
		$parts = array_map(fn ($t) => trim($t, '"'), $matches[0]);

		return array_map(fn ($part) => self::parseToken($part), $parts);
	}

	/**
	 * Parse a single whitespace-delimited token.
	 *
	 * @throws ValidationException
	 */
	private static function parseToken(string $part): SearchToken
	{
		$colon_pos = strpos($part, ':');

		// No colon → plain text token.
		if ($colon_pos === false) {
			$is_prefix = str_ends_with($part, '*');
			$value = $is_prefix ? substr($part, 0, -1) : $part;

			return new SearchToken(null, null, null, $value, $is_prefix);
		}

		$modifier = strtolower(substr($part, 0, $colon_pos));
		$rest = substr($part, $colon_pos + 1);

		// Unknown modifier → treat the whole string as a plain-text term.
		if (!in_array($modifier, self::KNOWN_MODIFIERS, true)) {
			return new SearchToken(null, null, null, $part, false);
		}

		if ($rest === '') {
			throw ValidationException::withMessages(['term' => "Modifier '{$modifier}:' requires a value."]);
		}

		if ($modifier === 'rating') {
			return self::parseRatingToken($rest);
		}

		// Extract leading operator.
		[$operator, $value_rest] = self::extractOperator($rest);

		// Detect trailing *
		$is_prefix = false;
		if (str_ends_with($value_rest, '*')) {
			$is_prefix = true;
			$value_rest = substr($value_rest, 0, -1);
			if ($value_rest === '') {
				throw ValidationException::withMessages(['term' => "Modifier '{$modifier}:*' requires a non-empty prefix before '*'."]);
			}
		}

		// Validate blank value after stripping operator/wildcard.
		if ($value_rest === '') {
			throw ValidationException::withMessages(['term' => "Modifier '{$modifier}:' requires a non-empty value."]);
		}

		self::validateModifierValue($modifier, $operator, $value_rest);

		return new SearchToken($modifier, null, $operator, $value_rest, $is_prefix);
	}

	/**
	 * Parse the portion of a `rating:` token after the first colon.
	 * Expected format: `sub_modifier:op:value`, e.g. `avg:>=4` or `own:>=3`.
	 *
	 * @throws ValidationException
	 */
	private static function parseRatingToken(string $rest): SearchToken
	{
		$colon_pos = strpos($rest, ':');
		if ($colon_pos === false) {
			throw ValidationException::withMessages(['term' => 'Invalid rating token. Expected format: rating:avg:>=4 or rating:own:>=3.']);
		}

		$sub_modifier = strtolower(substr($rest, 0, $colon_pos));
		if (!in_array($sub_modifier, ['avg', 'own'], true)) {
			throw ValidationException::withMessages(['term' => "Invalid rating sub-modifier '{$sub_modifier}'. Use 'avg' or 'own'."]);
		}

		$op_val = substr($rest, $colon_pos + 1);
		[$operator, $value] = self::extractOperator($op_val);

		if ($operator === null) {
			throw ValidationException::withMessages(['term' => 'Invalid rating token. An operator (<, <=, >, >=, =) is required before the value.']);
		}

		if ($value === '' || !ctype_digit($value)) {
			throw ValidationException::withMessages(['term' => "Invalid rating value '{$value}'. Expected a non-negative integer."]);
		}

		return new SearchToken('rating', $sub_modifier, $operator, $value, false);
	}

	/**
	 * Try to strip a leading comparison operator from the string.
	 *
	 * @return array{0: string|null, 1: string} [operator|null, remainder]
	 */
	private static function extractOperator(string $str): array
	{
		if (str_starts_with($str, '<=')) {
			return ['<=', substr($str, 2)];
		}
		if (str_starts_with($str, '>=')) {
			return ['>=', substr($str, 2)];
		}
		if (str_starts_with($str, '<')) {
			return ['<', substr($str, 1)];
		}
		if (str_starts_with($str, '>')) {
			return ['>', substr($str, 1)];
		}
		if (str_starts_with($str, '=')) {
			return ['=', substr($str, 1)];
		}

		return [null, $str];
	}

	/**
	 * Run modifier-specific validation on the parsed value.
	 *
	 * @throws ValidationException
	 */
	private static function validateModifierValue(string $modifier, ?string $operator, string $value): void
	{
		match ($modifier) {
			'date' => self::validateDate($value),
			'ratio' => self::validateRatio($operator, $value),
			'color', 'colour' => self::validateColour($value),
			default => null,
		};
	}

	/**
	 * @throws ValidationException
	 */
	private static function validateDate(string $value): void
	{
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 0) {
			throw ValidationException::withMessages(['term' => "Invalid date '{$value}'. Expected format: YYYY-MM-DD."]);
		}

		$parts = explode('-', $value);
		if (!checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0])) {
			throw ValidationException::withMessages(['term' => "Invalid calendar date '{$value}'."]);
		}
	}

	/**
	 * @throws ValidationException
	 */
	private static function validateRatio(?string $operator, string $value): void
	{
		// Named bucket is only valid without an operator.
		if ($operator === null && in_array($value, ['landscape', 'portrait', 'square'], true)) {
			return;
		}

		// Numeric comparison requires an operator and a positive float.
		if ($operator !== null) {
			if (!is_numeric($value) || (float) $value <= 0) {
				throw ValidationException::withMessages(['term' => "Invalid ratio value '{$value}'. Expected a positive number."]);
			}

			return;
		}

		throw ValidationException::withMessages(['term' => "Invalid ratio '{$value}'. Use 'landscape', 'portrait', 'square', or an operator+number like ratio:>1.5."]);
	}

	/**
	 * Validates a colour value.  Hex strings are validated strictly;
	 * plain words are passed through (resolved in ColourStrategy).
	 *
	 * @throws ValidationException
	 */
	private static function validateColour(string $value): void
	{
		if (str_starts_with($value, '#') && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 0) {
			throw ValidationException::withMessages(['term' => "Invalid colour hex '{$value}'. Expected format: #rrggbb."]);
		}
	}
}
