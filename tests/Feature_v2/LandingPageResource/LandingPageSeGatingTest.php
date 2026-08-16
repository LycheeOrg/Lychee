<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\LandingPageResource;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Proves the SE-gated fields (layout, animation_preset, featured_items_enabled/mode)
 * resolve fail-safe: never throw, never leak the SE-only value to a non-SE requester.
 */
class LandingPageSeGatingTest extends BaseApiWithDataTest
{
	use RequireSE;

	private array $original = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (['landing_layout', 'landing_animation_preset', 'landing_featured_items_enabled', 'landing_featured_items_mode'] as $key) {
			$this->original[$key] = Configs::query()->where('key', '=', $key)->value('value');
		}
	}

	public function tearDown(): void
	{
		foreach ($this->original as $key => $value) {
			if ($value !== null) {
				Configs::set($key, $value);
			}
		}
		$this->resetSe();
		parent::tearDown();
	}

	public function testNonSeRequesterFallsBackToClassicLayout(): void
	{
		Configs::set('landing_layout', 'portfolio');

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('layout', 'classic');
	}

	public function testSeRequesterSeesConfiguredLayout(): void
	{
		Configs::set('landing_layout', 'portfolio');
		$this->requireSe();

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('layout', 'portfolio');
	}

	public function testNonSeRequesterFallsBackToClassicFadeAnimation(): void
	{
		Configs::set('landing_animation_preset', 'zoom_in');

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('animation_preset', 'classic_fade');
	}

	public function testSeRequesterSeesConfiguredAnimation(): void
	{
		Configs::set('landing_animation_preset', 'parallax_scroll');
		$this->requireSe();

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('animation_preset', 'parallax_scroll');
	}

	public function testNonSeRequesterNeverSeesFeaturedItems(): void
	{
		Configs::set('landing_featured_items_enabled', '1');
		Configs::set('landing_featured_items_mode', 'manual');

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('featured_items_enabled', false);
		$response->assertJsonPath('featured_items_mode', 'automatic');
		$response->assertJsonPath('featured_items', []);
	}

	public function testClassicLayoutIsAlwaysAvailableRegardlessOfSeStatus(): void
	{
		Configs::set('landing_layout', 'classic');

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('layout', 'classic');
	}
}
