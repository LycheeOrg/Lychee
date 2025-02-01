<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Unit;

use function Safe\file_get_contents;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Tests\AbstractTestCase;

class CopyrightTest extends AbstractTestCase
{
	public const COPYRIGHT = "<?php\n\n/**\n * SPDX-License-Identifier: MIT\n * Copyright (c) 2017-2018 Tobias Reich\n * Copyright (c) 2018-2025 LycheeOrg.\n */\n";
	private ConsoleSectionOutput $msgSection;
	private bool $failed = false;
	private int $length = 0;

	// Those are files that are not licensed under Lychee MIT license.
	private array $except = [
		'app/Metadata/Laminas/Unicode.php',
		'app/Contracts/Laminas/DecoratorInterface.php',
		'app/Enum/Traits/DecorateBackedEnum.php',
	];

	/**
	 * Iterate over the directories and check if the files contain the correct license and copyright info..
	 *
	 * @return void
	 */
	public function testCopyrightPresent(): void
	{
		$this->msgSection = (new ConsoleOutput())->section();
		$this->length = mb_strlen(self::COPYRIGHT);

		$dirs = ['app', 'database', 'tests', 'routes'];
		array_walk($dirs, fn ($dir) => $this->directoryIteration($dir));

		if ($this->failed) {
			$this->msgSection->writeln(sprintf("<error>Expected copyright notice at the beginning of the file:</error>\n%s", self::COPYRIGHT));
		}
		self::assertFalse($this->failed);
	}

	/**
	 * Given a directory, list all files & subdirectories.
	 * Then apply the check.
	 *
	 * @param string $dir directory to check
	 *
	 * @return void
	 */
	private function directoryIteration(string $dir): void
	{
		$startfolder = base_path($dir);
		$rdi = new \RecursiveDirectoryIterator($startfolder, \FilesystemIterator::SKIP_DOTS);
		$rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);

		/** @var \SplFileInfo $di */
		foreach ($rii as $di) {
			if ($di->isFile() && $di->isReadable() && $di->getExtension() === 'php') {
				$this->checkFile($di->getPathname());
			}
		}
	}

	/**
	 * Given a full path, check if the file contains the correct copyright.
	 *
	 * @param string $file filename
	 *
	 * @return void
	 */
	private function checkFile(string $file): void
	{
		// We only check the first X bytes, where X is the length of the string we want to compare against.
		// No need to read all the file, this is a performance optimization.
		$file_content = file_get_contents($file, false, null, 0, $this->length);

		if ($file_content !== self::COPYRIGHT) {
			if (!$this->isException($file)) {
				$this->msgSection->writeln(sprintf('<comment>Error:</comment> File %s misses the copyright notice.', $file));
				$this->msgSection->writeln($file_content);
				$this->failed = true;
			}
		}
	}

	/**
	 * We check if the file is in the except list.
	 * We cannot use in_array because it contains the full path which is dependant on the running system.
	 *
	 * @param string $file filename
	 *
	 * @return bool
	 */
	private function isException(string $file): bool
	{
		foreach ($this->except as $except) {
			if (str_ends_with($file, $except)) {
				return true;
			}
		}

		return false;
	}
}
