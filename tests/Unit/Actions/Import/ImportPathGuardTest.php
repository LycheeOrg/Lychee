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

namespace Tests\Unit\Actions\Import;

use App\Actions\Import\ImportPathGuard;
use App\Enum\ImportPathStatus;
use function Safe\exec;
use function Safe\mkdir;
use function Safe\realpath;
use function Safe\symlink;
use function Safe\touch;
use Tests\AbstractTestCase;

/**
 * Feature 115: containment checks for restricted import roots (NFR-115-02, NFR-115-03).
 */
class ImportPathGuardTest extends AbstractTestCase
{
	private string $base;
	private string $root;
	private string $outside;

	public function setUp(): void
	{
		parent::setUp();
		$tmp = sys_get_temp_dir() . '/lychee-guard-' . bin2hex(random_bytes(4));
		mkdir($tmp);
		$this->base = realpath($tmp);
		$this->root = $this->base . '/alice';
		$this->outside = $this->base . '/outside';
		mkdir($this->root . '/2026', 0777, true);
		mkdir($this->base . '/alice2');
		mkdir($this->outside);
		touch($this->outside . '/secret.jpg');
		symlink($this->root . '/2026', $this->root . '/link-inside');
		symlink($this->outside, $this->root . '/link-outside');
		symlink($this->outside . '/secret.jpg', $this->root . '/file-outside.jpg');
	}

	public function tearDown(): void
	{
		exec('rm -rf ' . escapeshellarg($this->base));
		parent::tearDown();
	}

	public function testNormaliseResolvesDotsLexically(): void
	{
		self::assertSame('/srv/import/bob', ImportPathGuard::normalise('/srv/import/alice/../bob'));
		self::assertSame('/srv/import', ImportPathGuard::normalise('/srv//import/./'));
		self::assertSame('/', ImportPathGuard::normalise('/../..'));
		self::assertSame('/', ImportPathGuard::normalise('/'));
	}

	public function testIsWithinUsesTrailingSeparator(): void
	{
		self::assertTrue(ImportPathGuard::isWithin('/srv/import/alice', '/srv/import/alice'));
		self::assertTrue(ImportPathGuard::isWithin('/srv/import/alice/x', '/srv/import/alice'));
		self::assertFalse(ImportPathGuard::isWithin('/srv/import/alice2', '/srv/import/alice'));
		self::assertTrue(ImportPathGuard::isWithin('/etc', '/'));
	}

	public function testRootItselfIsInside(): void
	{
		self::assertSame(ImportPathStatus::INSIDE, ImportPathGuard::check($this->root, $this->root));
	}

	public function testChildIsInside(): void
	{
		self::assertSame(ImportPathStatus::INSIDE, ImportPathGuard::check($this->root . '/2026', $this->root));
	}

	public function testSiblingIsOutside(): void
	{
		self::assertSame(ImportPathStatus::OUTSIDE, ImportPathGuard::check($this->outside, $this->root));
	}

	public function testPrefixTrapIsOutside(): void
	{
		self::assertSame(ImportPathStatus::OUTSIDE, ImportPathGuard::check($this->base . '/alice2', $this->root));
	}

	public function testLexicalParentIsOutside(): void
	{
		self::assertSame(ImportPathStatus::OUTSIDE, ImportPathGuard::check($this->root . '/../outside', $this->root));
	}

	public function testMissingOutsideIsOutside(): void
	{
		self::assertSame(ImportPathStatus::OUTSIDE, ImportPathGuard::check('/does/not/exist', $this->root));
	}

	public function testRelativePathIsOutside(): void
	{
		self::assertSame(ImportPathStatus::OUTSIDE, ImportPathGuard::check('alice/2026', $this->root));
	}

	public function testMissingInsideIsMissing(): void
	{
		self::assertSame(ImportPathStatus::MISSING, ImportPathGuard::check($this->root . '/nope', $this->root));
	}

	public function testLinkInsideIsInside(): void
	{
		self::assertSame(ImportPathStatus::INSIDE, ImportPathGuard::check($this->root . '/link-inside', $this->root));
	}

	public function testLinkOutsideIsOutside(): void
	{
		self::assertSame(ImportPathStatus::OUTSIDE, ImportPathGuard::check($this->root . '/link-outside', $this->root));
		self::assertSame(ImportPathStatus::OUTSIDE, ImportPathGuard::check($this->root . '/file-outside.jpg', $this->root));
	}

	public function testRootReachedThroughLinkIsInside(): void
	{
		symlink($this->root, $this->base . '/alice-link');
		self::assertSame(ImportPathStatus::INSIDE, ImportPathGuard::check($this->base . '/alice-link/2026', $this->base . '/alice-link'));
	}

	public function testNoRoot(): void
	{
		self::assertSame(ImportPathStatus::INSIDE, ImportPathGuard::check($this->outside, null));
		self::assertSame(ImportPathStatus::INSIDE, ImportPathGuard::check($this->root . '/link-outside', null));
		self::assertSame(ImportPathStatus::MISSING, ImportPathGuard::check('/does/not/exist', null));
	}

	public function testRealRoot(): void
	{
		self::assertNull(ImportPathGuard::realRoot(null));
		self::assertNull(ImportPathGuard::realRoot('/'));
		self::assertSame($this->root, ImportPathGuard::realRoot($this->root . '/'));
		self::assertSame(ImportPathStatus::MISSING, ImportPathGuard::check($this->base . '/gone/x', $this->base . '/gone'));
	}
}
