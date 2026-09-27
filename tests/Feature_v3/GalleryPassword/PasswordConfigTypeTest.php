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

namespace Tests\Feature_v3\GalleryPassword;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 074 – write-only `password` config type (S-074-14, S-074-15).
 */
class PasswordConfigTypeTest extends BaseApiWithDataTest
{
	private string $initial_value = '';

	public function setUp(): void
	{
		parent::setUp();
		$this->initial_value = strval(Configs::query()->where('key', '=', 'gallery_password')->value('value'));
	}

	public function tearDown(): void
	{
		Configs::query()->where('key', '=', 'gallery_password')->update(['value' => $this->initial_value]);
		resolve(ConfigManager::class)->invalidateCache();
		parent::tearDown();
	}

	private function storedValue(): string
	{
		return strval(Configs::query()->where('key', '=', 'gallery_password')->value('value'));
	}

	private function setGalleryPassword(string $value): TestResponse
	{
		return $this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'gallery_password', 'value' => $value]],
		]);
	}

	/**
	 * @return array<string,mixed>|null
	 */
	private function findConfig(TestResponse $response, string $key): ?array
	{
		foreach ($response->json() as $category) {
			foreach ($category['configs'] as $config) {
				if ($config['key'] === $key) {
					return $config;
				}
			}
		}

		return null;
	}

	public function testAdminWriteStoresHash(): void
	{
		$this->assertOk($this->setGalleryPassword('secret'));

		self::assertNotSame('secret', $this->storedValue());
		self::assertTrue(Hash::check('secret', $this->storedValue()));
	}

	public function testTooShortPasswordIsRejected(): void
	{
		$this->assertUnprocessable($this->setGalleryPassword('abc'));
		self::assertSame($this->initial_value, $this->storedValue());
	}

	public function testEmptyValueClearsPassword(): void
	{
		$this->setGalleryPassword('secret');
		$this->assertOk($this->setGalleryPassword(''));

		self::assertSame('', $this->storedValue());
	}

	public function testOmittedKeyLeavesPasswordUnchanged(): void
	{
		$this->setGalleryPassword('secret');
		$hash = $this->storedValue();

		$this->assertOk($this->actingAs($this->admin)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'gallery_password_cookie_lifetime', 'value' => '7']],
		]));

		self::assertSame($hash, $this->storedValue());
	}

	public function testNonAdminCannotWrite(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Settings::setConfigs', [
			'configs' => [['key' => 'gallery_password', 'value' => 'secret']],
		]);

		$this->assertForbidden($response);
		self::assertSame($this->initial_value, $this->storedValue());
	}

	public function testReadsNeverReturnTheHash(): void
	{
		$write = $this->setGalleryPassword('secret');
		$hash = $this->storedValue();

		$config = $this->findConfig($write, 'gallery_password');
		self::assertNotNull($config);
		self::assertSame('', $config['value']);
		self::assertTrue($config['is_set']);

		$all = $this->actingAs($this->admin)->getJson('Settings');
		$this->assertOk($all);
		$config = $this->findConfig($all, 'gallery_password');
		self::assertNotNull($config);
		self::assertSame('', $config['value']);
		self::assertTrue($config['is_set']);
		self::assertStringNotContainsString($hash, $all->getContent());

		self::assertStringNotContainsString($hash, $this->getJson('Gallery::Init')->getContent());
	}

	public function testIsSetFalseWhenCleared(): void
	{
		$this->setGalleryPassword('');

		$config = $this->findConfig($this->actingAs($this->admin)->getJson('Settings'), 'gallery_password');
		self::assertNotNull($config);
		self::assertFalse($config['is_set']);
	}
}
