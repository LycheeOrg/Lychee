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

namespace Tests\Feature_v2\Album;

use App\Models\Configs;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 098 (FR-098-01/02, S-098-01..04): the tag and person album
 * category flags of `RootConfig`, as returned by `GET /Albums`.
 */
class RootConfigSeparateCategoriesTest extends BaseApiWithDataTest
{
	/** @var array<string,string> */
	private array $saved_configs = [];

	public function tearDown(): void
	{
		try {
			foreach ($this->saved_configs as $key => $value) {
				Configs::set($key, $value);
			}
		} finally {
			$this->saved_configs = [];
			parent::tearDown();
		}
	}

	private function setConfig(string $key, string $value): void
	{
		if (!array_key_exists($key, $this->saved_configs)) {
			$this->saved_configs[$key] = (string) Configs::query()->where('key', '=', $key)->value('value');
		}
		Configs::set($key, $value);
	}

	public function testConfigsDefaultToOff(): void
	{
		self::assertSame('0', Configs::query()->where('key', '=', 'TA_separate_category')->value('value'));
		self::assertSame('0', Configs::query()->where('key', '=', 'PA_separate_category')->value('value'));

		$response = $this->getJson('Albums');
		$this->assertOk($response);
		self::assertFalse($response->json('config.is_tag_albums_separate_category_enabled'));
		self::assertFalse($response->json('config.is_person_albums_separate_category_enabled'));
	}

	/**
	 * @return array<string,array{0:string,1:string,2:bool,3:bool}>
	 */
	public static function flagsProvider(): array
	{
		return [
			'tag only' => ['1', '0', true, false],
			'person only' => ['0', '1', false, true],
			'both' => ['1', '1', true, true],
		];
	}

	#[DataProvider('flagsProvider')]
	public function testFlagsFollowConfigs(string $tag, string $person, bool $expected_tag, bool $expected_person): void
	{
		$this->setConfig('TA_separate_category', $tag);
		$this->setConfig('PA_separate_category', $person);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($response);
		self::assertSame($expected_tag, $response->json('config.is_tag_albums_separate_category_enabled'));
		self::assertSame($expected_person, $response->json('config.is_person_albums_separate_category_enabled'));
	}
}
