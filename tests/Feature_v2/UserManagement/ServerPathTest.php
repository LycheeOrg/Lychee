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

namespace Tests\Feature_v2\UserManagement;

use App\Models\User;
use function Safe\exec;
use function Safe\mkdir;
use function Safe\realpath;
use function Safe\touch;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 115: FR-115-02, S-115-19.
 */
class ServerPathTest extends BaseApiWithDataTest
{
	private string $base;

	public function setUp(): void
	{
		parent::setUp();
		$tmp = sys_get_temp_dir() . '/lychee-server-path-' . bin2hex(random_bytes(4));
		mkdir($tmp . '/alice', 0777, true);
		$this->base = realpath($tmp);
	}

	public function tearDown(): void
	{
		exec('rm -rf ' . escapeshellarg($this->base));
		parent::tearDown();
	}

	private function create(?string $server_path, bool $send = true): \Illuminate\Testing\TestResponse
	{
		$payload = [
			'username' => 'alice',
			'password' => 'password',
			'may_upload' => true,
			'may_edit_own_settings' => false,
		];
		if ($send) {
			$payload['server_path'] = $server_path;
		}

		return $this->actingAs($this->admin)->postJson('UserManagement', $payload);
	}

	private function edit(?string $server_path, bool $send = true): \Illuminate\Testing\TestResponse
	{
		$payload = [
			'id' => $this->userMayUpload1->id,
			'username' => $this->userMayUpload1->username,
			'may_upload' => true,
			'may_edit_own_settings' => false,
		];
		if ($send) {
			$payload['server_path'] = $server_path;
		}

		return $this->actingAs($this->admin)->patchJson('UserManagement', $payload);
	}

	public function testCreateStoresNormalisedPath(): void
	{
		$response = $this->create($this->base . '//alice/');
		$this->assertCreated($response);
		$response->assertJsonFragment(['server_path' => $this->base . '/alice']);
		self::assertSame($this->base . '/alice', User::query()->where('username', '=', 'alice')->first()->server_path);
	}

	public function testCreateFullAccess(): void
	{
		$this->assertCreated($this->create('/'));
		self::assertSame('/', User::query()->where('username', '=', 'alice')->first()->server_path);
	}

	public function testCreateEmptyOrAbsentIsNull(): void
	{
		$this->assertCreated($this->create(''));
		self::assertNull(User::query()->where('username', '=', 'alice')->first()->server_path);
	}

	public function testCreateAbsentIsNull(): void
	{
		$this->assertCreated($this->create(null, false));
		self::assertNull(User::query()->where('username', '=', 'alice')->first()->server_path);
	}

	public function testRelativePathRefused(): void
	{
		$response = $this->create('srv/x');
		$this->assertUnprocessable($response);
		$response->assertJsonValidationErrors('server_path');
	}

	public function testDotDotRefused(): void
	{
		$this->assertUnprocessable($this->create($this->base . '/alice/../alice'));
		$this->assertUnprocessable($this->create($this->base . '/./alice'));
	}

	public function testMissingDirectoryRefused(): void
	{
		$this->assertUnprocessable($this->create($this->base . '/bob'));
	}

	public function testFileRefused(): void
	{
		touch($this->base . '/alice/file.txt');
		$this->assertUnprocessable($this->create($this->base . '/alice/file.txt'));
	}

	public function testEditSetsKeepsAndClears(): void
	{
		$this->assertNoContent($this->edit($this->base . '/alice/'));
		self::assertSame($this->base . '/alice', $this->userMayUpload1->fresh()->server_path);

		$this->assertNoContent($this->edit(null, false));
		self::assertSame($this->base . '/alice', $this->userMayUpload1->fresh()->server_path);

		$this->assertNoContent($this->edit(''));
		self::assertNull($this->userMayUpload1->fresh()->server_path);

		$this->assertNoContent($this->edit('/'));
		self::assertSame('/', $this->userMayUpload1->fresh()->server_path);

		$this->assertNoContent($this->edit(null));
		self::assertNull($this->userMayUpload1->fresh()->server_path);
	}

	public function testEditRefusesInvalid(): void
	{
		$this->assertUnprocessable($this->edit($this->base . '/missing'));
	}

	public function testListReturnsField(): void
	{
		$this->userMayUpload1->server_path = $this->base . '/alice';
		$this->userMayUpload1->save();

		$response = $this->actingAs($this->admin)->getJson('UserManagement');
		$this->assertOk($response);
		$response->assertJsonFragment(['id' => $this->userMayUpload1->id, 'server_path' => $this->base . '/alice']);
	}
}
