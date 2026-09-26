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

use App\Actions\Diagnostics\Pipes\Checks\GDSupportCheck;
use App\DTO\DiagnosticData;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Lossless WebP warning of the GD diagnostic (Q-071-07).
 */
class GDSupportCheckTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function check(bool $supports_lossless_webp): GDSupportCheck
	{
		$config_manager = resolve(ConfigManager::class);
		$config_manager->invalidateCache();

		return new class($config_manager, $supports_lossless_webp) extends GDSupportCheck {
			public function __construct(ConfigManager $config_manager, private bool $supports)
			{
				parent::__construct($config_manager);
			}

			protected function supportsLosslessWebp(): bool
			{
				return $this->supports;
			}
		};
	}

	/**
	 * @return string[]
	 */
	private function messages(GDSupportCheck $check): array
	{
		$data = [];

		return array_map(fn (DiagnosticData $d) => $d->message, $check->handle($data, fn (array $d) => $d));
	}

	private function configure(string $format, int $quality): void
	{
		Configs::set('imagick', false);
		Configs::set('size_variant_format', $format);
		Configs::set('compression_quality', $quality);
	}

	public function testWarnsWhenLosslessWebpIsRequestedButUnsupported(): void
	{
		$this->configure('webp', 0);

		self::assertContains(GDSupportCheck::LOSSLESS_WEBP_UNSUPPORTED, $this->messages($this->check(false)));
	}

	public function testNoWarningWhenLosslessWebpIsSupported(): void
	{
		$this->configure('webp', 0);

		self::assertNotContains(GDSupportCheck::LOSSLESS_WEBP_UNSUPPORTED, $this->messages($this->check(true)));
	}

	public function testNoWarningForLossyQuality(): void
	{
		$this->configure('webp', 80);

		self::assertNotContains(GDSupportCheck::LOSSLESS_WEBP_UNSUPPORTED, $this->messages($this->check(false)));
	}

	public function testNoWarningForOtherFormats(): void
	{
		$this->configure('jpeg', 0);

		self::assertNotContains(GDSupportCheck::LOSSLESS_WEBP_UNSUPPORTED, $this->messages($this->check(false)));
	}
}
