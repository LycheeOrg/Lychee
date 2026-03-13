<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\Search;

use App\Actions\Search\SearchTokenParser;
use App\DTO\Search\SearchToken;
use Illuminate\Validation\ValidationException;
use Tests\AbstractTestCase;

class SearchTokenParserTest extends AbstractTestCase
{
	// ---------------------------------------------------------------------------
	// Plain text
	// ---------------------------------------------------------------------------

	public function testSinglePlainTermProducesNullModifier(): void
	{
		$tokens = SearchTokenParser::parse('hello');
		$this->assertCount(1, $tokens);
		$this->assertNull($tokens[0]->modifier);
		$this->assertSame('hello', $tokens[0]->value);
		$this->assertFalse($tokens[0]->is_prefix);
	}

	public function testMultiplePlainTermsProduceMultipleTokens(): void
	{
		$tokens = SearchTokenParser::parse('foo bar baz');
		$this->assertCount(3, $tokens);
		foreach ($tokens as $token) {
			$this->assertNull($token->modifier);
		}
	}

	public function testQuotedStringCountsAsSinglePlainToken(): void
	{
		$tokens = SearchTokenParser::parse('"hello world"');
		$this->assertCount(1, $tokens);
		$this->assertSame('hello world', $tokens[0]->value);
		$this->assertNull($tokens[0]->modifier);
	}

	public function testUnknownModifierFallsBackToPlainText(): void
	{
		$tokens = SearchTokenParser::parse('unknownmod:value');
		$this->assertCount(1, $tokens);
		$this->assertNull($tokens[0]->modifier);
		$this->assertSame('unknownmod:value', $tokens[0]->value);
	}

	// ---------------------------------------------------------------------------
	// Prefix modifier (token ends with *)
	// ---------------------------------------------------------------------------

	public function testPlainTermWithStarIsPrefix(): void
	{
		$tokens = SearchTokenParser::parse('hel*');
		$this->assertCount(1, $tokens);
		$this->assertNull($tokens[0]->modifier);
		$this->assertSame('hel', $tokens[0]->value);
		$this->assertTrue($tokens[0]->is_prefix);
	}

	// ---------------------------------------------------------------------------
	// Tag modifier
	// ---------------------------------------------------------------------------

	public function testTagModifierParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('tag:sunset');
		$this->assertCount(1, $tokens);
		$this->assertSame('tag', $tokens[0]->modifier);
		$this->assertSame('sunset', $tokens[0]->value);
	}

	public function testTagWildcardThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('tag:*');
	}

	// ---------------------------------------------------------------------------
	// Date modifier
	// ---------------------------------------------------------------------------

	public function testDateExactParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('date:2024-05-01');
		$this->assertCount(1, $tokens);
		$this->assertSame('date', $tokens[0]->modifier);
		$this->assertSame('2024-05-01', $tokens[0]->value);
		$this->assertNull($tokens[0]->operator);
	}

	public function testDateWithOperatorParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('date:>=2024-01-01');
		$this->assertCount(1, $tokens);
		$this->assertSame('date', $tokens[0]->modifier);
		$this->assertSame('>=', $tokens[0]->operator);
		$this->assertSame('2024-01-01', $tokens[0]->value);
	}

	public function testInvalidDateThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('date:not-a-date');
	}

	public function testDateWithEmptyValueThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('date:');
	}

	public function testDateWithInvalidCalendarDateThrowsValidationException(): void
	{
		// Month 13 has valid YYYY-MM-DD format but fails checkdate().
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('date:2024-13-01');
	}

	// ---------------------------------------------------------------------------
	// Colour modifier
	// ---------------------------------------------------------------------------

	public function testColourHexParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('colour:#ff0000');
		$this->assertCount(1, $tokens);
		$this->assertSame('colour', $tokens[0]->modifier);
		$this->assertSame('#ff0000', $tokens[0]->value);
	}

	public function testColorAliasIsKnownModifier(): void
	{
		$tokens = SearchTokenParser::parse('color:#00ff00');
		$this->assertCount(1, $tokens);
		$this->assertSame('color', $tokens[0]->modifier);
	}

	public function testInvalidColourThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('colour:#xyz');
	}

	// ---------------------------------------------------------------------------
	// Ratio modifier
	// ---------------------------------------------------------------------------

	public function testRatioLandscapeParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('ratio:landscape');
		$this->assertCount(1, $tokens);
		$this->assertSame('ratio', $tokens[0]->modifier);
		$this->assertSame('landscape', $tokens[0]->value);
	}

	public function testRatioPortraitParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('ratio:portrait');
		$this->assertCount(1, $tokens);
		$this->assertSame('portrait', $tokens[0]->value);
	}

	public function testRatioSquareParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('ratio:square');
		$this->assertCount(1, $tokens);
		$this->assertSame('square', $tokens[0]->value);
	}

	public function testInvalidRatioThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('ratio:widescreen');
	}

	public function testRatioNumericWithoutOperatorThrowsValidationException(): void
	{
		// ratio:1.5 is a valid number but has no operator and is not a named bucket.
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('ratio:1.5');
	}

	public function testRatioNonNumericValueThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('ratio:>notanumber');
	}

	public function testRatioNumericWithOperatorParsesCorrectly(): void
	{
		$tokens = SearchTokenParser::parse('ratio:>1.5');
		$this->assertCount(1, $tokens);
		$this->assertSame('ratio', $tokens[0]->modifier);
		$this->assertSame('>', $tokens[0]->operator);
		$this->assertSame('1.5', $tokens[0]->value);
	}

	// ---------------------------------------------------------------------------
	// Rating modifier
	// ---------------------------------------------------------------------------

	public function testRatingAvgWithOperator(): void
	{
		$tokens = SearchTokenParser::parse('rating:avg:>=3');
		$this->assertCount(1, $tokens);
		$this->assertSame('rating', $tokens[0]->modifier);
		$this->assertSame('avg', $tokens[0]->sub_modifier);
		$this->assertSame('>=', $tokens[0]->operator);
		$this->assertSame('3', $tokens[0]->value);
	}

	public function testRatingOwnWithExact(): void
	{
		$tokens = SearchTokenParser::parse('rating:own:=5');
		$this->assertCount(1, $tokens);
		$this->assertSame('own', $tokens[0]->sub_modifier);
		$this->assertSame('=', $tokens[0]->operator);
		$this->assertSame('5', $tokens[0]->value);
	}

	public function testRatingWithoutsubModifierThrowsValidationException(): void
	{
		// rating:>=4 is not supported — a sub-modifier (avg/own) is required.
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('rating:>=4');
	}

	public function testRatingBareNumberThrowsValidationException(): void
	{
		// rating:5 is not supported — a sub-modifier and operator are required.
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('rating:5');
	}

	public function testUnknownRatingsubModifierThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('rating:best:>=3');
	}

	public function testRatingAvgWithNoOperatorThrowsValidationException(): void
	{
		// rating:avg:5 has a sub-modifier but no comparison operator.
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('rating:avg:5');
	}

	public function testRatingNonDigitValueThrowsValidationException(): void
	{
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('rating:avg:>=abc');
	}

	// ---------------------------------------------------------------------------
	// EXIF field modifiers
	// ---------------------------------------------------------------------------

	public function testExifMakeModifier(): void
	{
		$tokens = SearchTokenParser::parse('make:Canon');
		$this->assertCount(1, $tokens);
		$this->assertSame('make', $tokens[0]->modifier);
		$this->assertSame('Canon', $tokens[0]->value);
	}

	public function testFieldModifierWithOnlyWildcardThrowsValidationException(): void
	{
		// make:* is forbidden — the prefix value before * must not be empty.
		$this->expectException(ValidationException::class);
		SearchTokenParser::parse('make:*');
	}

	public function testIsoPrefixModifier(): void
	{
		$tokens = SearchTokenParser::parse('iso:160*');
		$this->assertCount(1, $tokens);
		$this->assertSame('iso', $tokens[0]->modifier);
		$this->assertSame('160', $tokens[0]->value);
		$this->assertTrue($tokens[0]->is_prefix);
	}

	// ---------------------------------------------------------------------------
	// Mixed query
	// ---------------------------------------------------------------------------

	public function testMixedQueryProducesCorrectTokens(): void
	{
		$tokens = SearchTokenParser::parse('sunset tag:beach date:>=2023-01-01');
		$this->assertCount(3, $tokens);
		$modifiers = array_map(fn (SearchToken $t) => $t->modifier, $tokens);
		$this->assertContains(null, $modifiers);
		$this->assertContains('tag', $modifiers);
		$this->assertContains('date', $modifiers);
	}

	public function testEmptyStringReturnsEmptyArray(): void
	{
		$tokens = SearchTokenParser::parse('');
		$this->assertCount(0, $tokens);
	}

	public function testWhitespaceOnlyReturnsEmptyArray(): void
	{
		$tokens = SearchTokenParser::parse('   ');
		$this->assertCount(0, $tokens);
	}
}
