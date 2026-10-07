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

namespace Tests\Feature_v3\LoadingIndicator;

use Tests\Feature_v3\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Feature 084 – the loading indicator mode is rendered into the HTML page (FR-084-02 … FR-084-04).
 */
class LoadingIndicatorWebTest extends BaseApiWithDataTest
{
	use RequireSE;

	public function tearDown(): void
	{
		config(['features.white_label_enabled' => false, 'features.loading_indicator_url' => '']);
		$this->resetSe();
		parent::tearDown();
	}

	public function testDefaultWithoutSe(): void
	{
		$this->assertMetaTag('default', '');
	}

	public function testDefaultWithoutSeEvenWhenConfigured(): void
	{
		config(['features.white_label_enabled' => true, 'features.loading_indicator_url' => 'dist/loading.gif']);

		$this->assertMetaTag('default', '');
	}

	public function testDefaultWithSeOnly(): void
	{
		$this->requireSe();

		$this->assertMetaTag('default', '');
	}

	public function testDefaultWithSeAndBlankUrl(): void
	{
		$this->requireSe();
		config(['features.loading_indicator_url' => '   ']);

		$this->assertMetaTag('default', '');
	}

	public function testUnbrandedWithSeAndWhiteLabel(): void
	{
		$this->requireSe();
		config(['features.white_label_enabled' => true]);

		$this->assertMetaTag('unbranded', '');
	}

	public function testCustomWithSeAndUrl(): void
	{
		$this->requireSe();
		config(['features.white_label_enabled' => true, 'features.loading_indicator_url' => 'dist/loading.gif']);

		$this->assertMetaTag('custom', 'dist/loading.gif');
	}

	public function testCustomWithSeAndUrlWithoutWhiteLabel(): void
	{
		$this->requireSe();
		config(['features.loading_indicator_url' => 'dist/loading.gif']);

		$this->assertMetaTag('custom', 'dist/loading.gif');
	}

	private function assertMetaTag(string $mode, string $url): void
	{
		$response = $this->get('/gallery');

		$this->assertOk($response);
		$response->assertSee('<meta name="lychee-loading-indicator" content="' . $mode . '" data-url="' . $url . '">', false);
	}
}
