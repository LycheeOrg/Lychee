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
use App\Models\LandingLink;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresEmptyLandingLinks;

class LandingPageContentTest extends BaseApiWithDataTest
{
	use RequiresEmptyLandingLinks;

	private array $original = [];

	private const KEYS = [
		'landing_intro_screen_enabled', 'landing_hero_text_position', 'landing_hero_text_color',
		'landing_hero_text_opacity', 'landing_about_enabled', 'landing_about_text', 'landing_cta_text',
	];

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyLandingLinks();
		foreach (self::KEYS as $key) {
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
		$this->tearDownRequiresEmptyLandingLinks();
		parent::tearDown();
	}

	public function testDefaultsMatchPreFeatureBehaviour(): void
	{
		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('layout', 'classic');
		$response->assertJsonPath('intro_screen_enabled', true);
		$response->assertJsonPath('hero_text_position', 'center');
		$response->assertJsonPath('hero_text_color', '');
		$response->assertJsonPath('hero_text_opacity', 100);
		$response->assertJsonPath('animation_preset', 'classic_fade');
		$response->assertJsonPath('about_enabled', false);
		$response->assertJsonPath('featured_items_enabled', false);
		$response->assertJsonPath('cta_text', '');
		$response->assertJsonPath('links', []);
	}

	public function testPassthroughFieldsReflectConfiguredValues(): void
	{
		Configs::set('landing_intro_screen_enabled', '0');
		Configs::set('landing_hero_text_position', 'bottom_right');
		Configs::set('landing_hero_text_color', '#123456');
		Configs::set('landing_hero_text_opacity', '40');
		Configs::set('landing_about_enabled', '1');
		Configs::set('landing_about_text', '<p>About us</p>');
		Configs::set('landing_cta_text', 'Enter now');

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('intro_screen_enabled', false);
		$response->assertJsonPath('hero_text_position', 'bottom_right');
		$response->assertJsonPath('hero_text_color', '#123456');
		$response->assertJsonPath('hero_text_opacity', 40);
		$response->assertJsonPath('about_enabled', true);
		$response->assertJsonPath('about_text', '<p>About us</p>');
		$response->assertJsonPath('cta_text', 'Enter now');
	}

	public function testLinksAreEnabledOnlyAndOrderedBySortOrder(): void
	{
		LandingLink::factory()->create(['label' => 'Second', 'sort_order' => 1, 'enabled' => true]);
		LandingLink::factory()->create(['label' => 'Disabled', 'sort_order' => 0, 'enabled' => false]);
		LandingLink::factory()->create(['label' => 'First', 'sort_order' => 0, 'enabled' => true]);

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonCount(2, 'links');
		$response->assertJsonPath('links.0.label', 'First');
		$response->assertJsonPath('links.1.label', 'Second');
	}
}
