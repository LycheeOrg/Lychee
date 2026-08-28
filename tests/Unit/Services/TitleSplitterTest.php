<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Services;

use App\Services\TitleSplitter;
use Tests\AbstractTestCase;

/**
 * Unit tests for {@link TitleSplitter::split()} (Feature 060, FR-060-02).
 */
class TitleSplitterTest extends AbstractTestCase
{
	public function testTrailingDigits(): void
	{
		$result = TitleSplitter::split('test10');
		$this->assertSame('test', $result->base);
		$this->assertSame(10, $result->index);
	}

	public function testTrailingDigitsFamily(): void
	{
		// S-060-01/02: test_0, test_1, test_2, test_10 must share the same base.
		foreach (['test_0' => 0, 'test_1' => 1, 'test_2' => 2, 'test_10' => 10] as $title => $expected_index) {
			$result = TitleSplitter::split($title);
			$this->assertSame('test_', $result->base);
			$this->assertSame($expected_index, $result->index);
		}
	}

	public function testParenthesisedFallback(): void
	{
		// S-060-03
		$result = TitleSplitter::split('Photo (2)');
		$this->assertSame('photo ', $result->base);
		$this->assertSame(2, $result->index);

		$result = TitleSplitter::split('Photo (10)');
		$this->assertSame('photo ', $result->base);
		$this->assertSame(10, $result->index);
	}

	public function testNoDigitFallback(): void
	{
		// S-060-04
		$result = TitleSplitter::split('Vacation');
		$this->assertSame('vacation', $result->base);
		$this->assertNull($result->index);
	}

	public function testCaseFold(): void
	{
		// S-060-05
		$apple = TitleSplitter::split('Apple');
		$banana = TitleSplitter::split('banana');
		$cherry = TitleSplitter::split('Cherry');

		$this->assertSame('apple', $apple->base);
		$this->assertSame('banana', $banana->base);
		$this->assertSame('cherry', $cherry->base);
	}

	public function testDigitRunLongerThan19CharactersIsTruncatedToLast19(): void
	{
		// 25-digit run -> keep only the last 19 digits.
		$digits = '1234567890123456789012345';
		$expected = substr($digits, -19);

		$result = TitleSplitter::split('test' . $digits);

		$this->assertSame('test', $result->base);
		$this->assertSame((int) $expected, $result->index);
	}

	public function testEmptyString(): void
	{
		$result = TitleSplitter::split('');
		$this->assertSame('', $result->base);
		$this->assertNull($result->index);
	}

	public function testUnicodeTitle(): void
	{
		$result = TitleSplitter::split('Café 5');
		$this->assertSame('café ', $result->base);
		$this->assertSame(5, $result->index);

		$result = TitleSplitter::split('Ünïcödé');
		$this->assertSame('ünïcödé', $result->base);
		$this->assertNull($result->index);
	}

	/**
	 * S-060-16: extension-shaped suffix is stripped before the trailing-digit
	 * rule runs, then re-appended to `base` (NFR-060-09) once the index is
	 * successfully extracted.
	 */
	public function testExtensionStrippedBeforeTrailingDigitsRule(): void
	{
		$result = TitleSplitter::split('xxx_123.jpg');
		$this->assertSame('xxx_.jpg', $result->base);
		$this->assertSame(123, $result->index);
	}

	/**
	 * S-060-17: parenthesised-number rule, no extension involved.
	 */
	public function testParenthesisedNumberWithoutExtension(): void
	{
		$result = TitleSplitter::split('xxx (123)');
		$this->assertSame('xxx ', $result->base);
		$this->assertSame(123, $result->index);
	}

	/**
	 * S-060-18: extension stripped before the parenthesised-number rule runs,
	 * then re-appended to `base` (NFR-060-09).
	 */
	public function testExtensionStrippedBeforeParenthesisedRule(): void
	{
		$result = TitleSplitter::split('xxx (123).xts');
		$this->assertSame('xxx .xts', $result->base);
		$this->assertSame(123, $result->index);
	}

	/**
	 * S-060-19: a digit-only suffix ("`.2`") is never treated as an
	 * extension, so the trailing-digit rule matches the untouched string.
	 */
	public function testDigitOnlySuffixIsNeverTreatedAsExtension(): void
	{
		$result = TitleSplitter::split('xxx.2');
		$this->assertSame('xxx.', $result->base);
		$this->assertSame(2, $result->index);
	}

	/**
	 * S-060-20: a false-positive extension guess on a non-digit suffix falls
	 * through to the fallback rule, using the full, un-stripped original
	 * title - no information is silently lost.
	 */
	public function testFalsePositiveExtensionGuessFallsBackToFullOriginalTitle(): void
	{
		$result = TitleSplitter::split('Vol.II');
		$this->assertSame('vol.ii', $result->base);
		$this->assertNull($result->index);
	}

	/**
	 * S-060-21/NFR-060-09: titles that differ only by file extension but
	 * share the same numeric-suffix stem get different `base` values and
	 * therefore do not tie/interleave by index across extensions.
	 */
	public function testMixedExtensionPairDoesNotTie(): void
	{
		$jpg = TitleSplitter::split('photo_5.jpg');
		$heic = TitleSplitter::split('photo_5.heic');

		$this->assertSame('photo_.jpg', $jpg->base);
		$this->assertSame('photo_.heic', $heic->base);
		$this->assertSame(5, $jpg->index);
		$this->assertSame(5, $heic->index);
		$this->assertNotSame($jpg->base, $heic->base);
	}
}
