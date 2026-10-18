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

namespace Tests\Unit\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\ImagickJxlCheck;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * JPEG XL support line of the diagnostics (Feature 100, FR-100-07, S-100-09..11).
 */
class ImagickJxlCheckTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function check(bool $supports_jxl): ImagickJxlCheck
	{
		$config_manager = resolve(ConfigManager::class);
		$config_manager->invalidateCache();

		return new class($config_manager, $supports_jxl) extends ImagickJxlCheck {
			public function __construct(ConfigManager $config_manager, private bool $supports)
			{
				parent::__construct($config_manager);
			}

			protected function supportsJxl(): bool
			{
				return $this->supports;
			}
		};
	}

	/**
	 * @return DiagnosticData[]
	 */
	private function lines(ImagickJxlCheck $check): array
	{
		$data = [];

		return $check->handle($data, fn (array $d) => $d);
	}

	public function testInfoWhenUnsupported(): void
	{
		// S-100-09
		Configs::set('size_variant_format', 'webp');

		$lines = $this->lines($this->check(false));

		self::assertCount(1, $lines);
		self::assertSame(MessageType::INFO, $lines[0]->type);
		self::assertSame(ImagickJxlCheck::UNSUPPORTED, $lines[0]->message);
	}

	public function testErrorWhenUnsupportedAndJxlSelected(): void
	{
		// S-100-10
		Configs::set('size_variant_format', 'jxl');

		$lines = $this->lines($this->check(false));

		self::assertCount(1, $lines);
		self::assertSame(MessageType::ERROR, $lines[0]->type);
		self::assertSame(ImagickJxlCheck::UNSUPPORTED_WITH_JXL_FORMAT, $lines[0]->message);
	}

	public function testNothingWhenSupported(): void
	{
		// S-100-11
		Configs::set('size_variant_format', 'jxl');

		self::assertSame([], $this->lines($this->check(true)));
	}
}
