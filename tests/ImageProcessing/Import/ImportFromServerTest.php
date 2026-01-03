<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\ImageProcessing\Import;

use Illuminate\Support\Facades\Queue;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ImportFromServerTest extends BaseApiWithDataTest
{
	private function getDefaultPayload(string $directory, ?string $album_id = null): array
	{
		return [
			'directories' => [$directory],
			'delete_imported' => false,
			'import_via_symlink' => false,
			'skip_duplicates' => false,
			'resync_metadata' => false,
			'delete_missing_photos' => false,
			'delete_missing_albums' => false,
			'album_id' => $album_id,
		];
	}

	public function testImportFromServertAsGuest(): void
	{
		$response = $this->postJson('/Import', $this->getDefaultPayload('tests'));
		$this->assertUnauthorized($response);
	}

	public function testImportFromServertLoggedIn(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('/Import', $this->getDefaultPayload('tests'));
		$this->assertForbidden($response);
	}

	public function testImportFromServertWrongDirNotAuthorized(): void
	{
		$payload = $this->getDefaultPayload('wrong-dir');
		$response = $this->actingAs($this->userLocked)->postJson('/Import', $payload);
		$this->assertForbidden($response);
	}

	public function testImportFromServertConflictingOptions(): void
	{
		$payload = $this->getDefaultPayload('tests');
		$payload['delete_imported'] = true;
		$payload['import_via_symlink'] = true;
		$response = $this->actingAs($this->userLocked)->postJson('/Import', $payload);
		$this->assertUnprocessable($response);
	}

	public function testImportFromServertWrongDir(): void
	{
		$payload = $this->getDefaultPayload('wrong-dir');
		$response = $this->actingAs($this->admin)->postJson('/Import', $payload);
		$this->assertUnprocessable($response);
	}

	public function testImportFromServerSuccess(): void
	{
		// No need to dispatch. :)
		Queue::fake();

		$directory = 'tests/Samples/sync';

		$response = $this->actingAs($this->admin)->postJson('/Import', $this->getDefaultPayload($directory, $this->album5->id));
		$this->assertCreated($response);
		$response->assertJsonStructure([
			'status',
			'message',
			'results' => [
				['directory', 'status', 'jobs_count'],
			],
			'job_count',
		]);
		$response->assertJson(['status' => true]);

		// Check that jobs were created
		$this->assertGreaterThan(0, $response->json('job_count'));
		Queue::assertCount($response->json('job_count') + 2); // +2 for the preprocessing job
	}
}
